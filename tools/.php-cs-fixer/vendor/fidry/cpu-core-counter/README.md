# CPU Core Counter

This package is a tiny utility to get the number of CPU cores.

```sh
composer require fidry/cpu-core-counter
```


## Usage

```php
use Fidry\CpuCoreCounter\CpuCoreCounter;
use Fidry\CpuCoreCounter\NumberOfCpuCoreNotFound;
use Fidry\CpuCoreCounter\Finder\DummyCpuCoreFinder;

$counter = new CpuCoreCounter();

// For knowing the number of cores you can use for launching parallel processes:
$counter->getAvailableForParallelisation()->availableCpus;

// Get the number of CPU cores (by default it will use the logical cores count).
// This count does not account for CPU quotas or limits, e.g. `docker run --cpus=2`
// or KUBERNETES_CPU_LIMIT. Use ::getAvailableForParallelisation() to account for them.
try {
    $counter->getCount();   // e.g. 8
} catch (NumberOfCpuCoreNotFound) {
    return 1;   // Fallback value
}

// Alternatively, to avoid having to catch the exception:

$counter = new CpuCoreCounter([
    ...CpuCoreCounter::getDefaultFinders(),
    new DummyCpuCoreFinder(1),  // Fallback value
]);

// A type-safe alternative form:
$counter->getCountWithFallback(1);

// Note that the result is memoized.
$counter->getCount();   // e.g. 8

```


## Advanced usage

### Changing the finders

When creating `CpuCoreCounter`, you can change the order of the finders or
disable specific ones by passing the list of finders to use:

```php
// Remove WindowsWmicFinder 
$finders = array_filter(
    CpuCoreCounter::getDefaultFinders(),
    static fn (CpuCoreFinder $finder) => !($finder instanceof WindowsWmicFinder)
);

$cores = (new CpuCoreCounter($finders))->getCount();
```

```php
// Use CPUInfo first & don't use Nproc
$finders = [
    new CpuInfoFinder(),
    new WindowsWmicFinder(),
    new HwLogicalFinder(),
];

$cores = (new CpuCoreCounter($finders))->getCount();
```

### Choosing only logical or physical finders

`FinderRegistry` provides two helpful entries:

- `::getDefaultLogicalFinders()`: gives an ordered list of finders that will
  look for the _logical_ CPU cores count.
- `::getDefaultPhysicalFinders()`: gives an ordered list of finders that will
  look for the _physical_ CPU cores count.

By default, `CpuCoreCounter` uses the logical finders, since this is usually
what you need and is also what the PHP source uses when building the PHP binary.


### Virtual machines

Inside a virtual machine (VM), such as a VMware or Parallels Desktop VM, a
micro-VM, a CI runner or a cloud instance, the library sees only the VM's CPUs,
not the host's. As a result:

- The count is the number of virtual CPUs (vCPUs) assigned to the VM. If the
  host assigns more vCPUs than it has cores, the count overstates what can
  actually run in parallel. This cannot be detected from inside the VM, so
  assign at most as many vCPUs as the host has cores.
- `NProcFinder` with `$all = true` (`nproc --all`) may also count CPUs that the
  hypervisor reserves for hot-adding: a 6-vCPU VMware VM can report 128. The
  default finders count only online CPUs. The `execute` and `diagnose` scripts
  still run this finder, so disregard its result there.
- A CPU limit set by the host on the VM is not visible. The cgroup CPU quota
  check (see `getAvailableForParallelisation()`) covers only cgroups within the
  VM, e.g. `docker run --cpus=2`. Use the `$countLimit` parameter of
  `getAvailableForParallelisation()` instead.
- The physical count reflects the CPU topology presented by the hypervisor, not
  the host's physical cores. It does not show whether vCPUs share a core (SMT)
  or run on slower cores.
- The load average covers only the processes inside the VM. A busy host can
  therefore report a low load, and `$loadLimit` will not reduce the result.


### Containers

A container, such as Docker or LXC, shares the host's kernel, so the library
sees the host's CPUs. As a result:

- Only `NProcFinder` and `CpuAffinityFinder` restrict the count to the CPUs the
  container may use, e.g. with `docker run --cpuset-cpus`. Other finders,
  including all physical ones, may count every CPU of the host. On Linux, they
  are the first default logical finders, so the default count is affected only
  when `nproc` is not available and neither is `/proc/self/status` or
  `/sys/devices/system/cpu/online`, or when you use other finders.
- A CPU quota set outside the container's cgroup namespace is not detected,
  e.g. with Proxmox VE LXC containers. Use `$countLimit` instead.
- The load average may be the host's, unless the container virtualises it
  (e.g. with LXCFS). Use `$systemLoadAverage` instead.


### Ignoring the system load

By default, `getAvailableForParallelisation()` ignores the system load. You
can use it only to get the number of CPU cores and the CPU limit, e.g. the
cgroup CPU quota:

```php
$result = $counter->getAvailableForParallelisation();

$result->totalCoresCount;       // e.g. 8
$result->correctedCountLimit;   // e.g. 2, or null if there is no limit
$result->availableCpus;         // e.g. 2
```

`sys_getloadavg()` is not available on Windows, and it can be disabled with
`disable_functions`. If you pass a `$loadLimit` and set `$systemLoadAverage`
to `null` there, the load average is treated as 0: `$loadLimit` still
applies, but the current load does not reduce the result.


### Inspecting what the finders find on your system

Three scripts provide insight into what the finders find:

```shell
# Executes every finder and displays the result it found.
make execute                                     # From this repository
./vendor/fidry/cpu-core-counter/bin/execute.php  # From the library

# Executes every finder with details about how the result was obtained.
make diagnose                                     # From this repository
./vendor/fidry/cpu-core-counter/bin/diagnose.php  # From the library

# Displays the trace of CpuCoreCounter with all finders, then with the default ones.
php bin/trace.php                              # From this repository
./vendor/fidry/cpu-core-counter/bin/trace.php  # From the library
```


### Debugging the results

Three approaches help understand how a result was obtained:

1. If you use the default finder registries, the scripts described in the
   previous section provide detailed information.
2. To understand how the number of CPU cores was found, use
   `CpuCoreCounter::trace()`.
3. To understand how the number of CPU cores available for parallelisation was
   calculated, inspect the `ParallelisationResult` returned by
   `CpuCoreCounter::getAvailableForParallelisation()`.


## Backward Compatibility Promise (BCP)

The policy largely follows [Symfony's][symfony-bc-policy]. Code marked as
`@private` or `@internal` is excluded from the BCP.

The following elements are also excluded:

- The `diagnose`, `execute` and `trace` scripts: they are intended for debugging
  and inspection only.
- `FinderRegistry::get*Finders()`: finders may be added or reordered at any
  time.


## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md) for how to set up the project, run the
tests, and understand the end-to-end tests and inspection builds.


## License

This package is licensed using the MIT License.

See [`LICENSE.md`](LICENSE.md) for details.

[symfony-bc-policy]: https://symfony.com/doc/current/contributing/code/bc.html
