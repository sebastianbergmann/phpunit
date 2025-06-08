<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Utils;

use Nette;


/**
 * DateTime.
 */
class DateTime extends \DateTime implements \JsonSerializable
{
	use Nette\SmartObject;

	/** minute in seconds */
	public const MINUTE = 60;

	/** hour in seconds */
	public const HOUR = 60 * self::MINUTE;

	/** day in seconds */
	public const DAY = 24 * self::HOUR;

	/** week in seconds */
	public const WEEK = 7 * self::DAY;

	/** average month in seconds */
	public const MONTH = 2_629_800;

	/** average year in seconds */
	public const YEAR = 31_557_600;


	/**
	 * Creates a DateTime object from a string, UNIX timestamp, or other DateTimeInterface object.
	 * @throws \Exception if the date and time are not valid.
	 */
	public static function from(string|int|\DateTimeInterface|null $time): static
	{
		if ($time instanceof \DateTimeInterface) {
			return new static($time->format('Y-m-d H:i:s.u'), $time->getTimezone());

		} elseif (is_numeric($time)) {
			if ($time <= self::YEAR) {
				$time += time();
			}

			return (new static)->setTimestamp((int) $time);

		} else { // textual or null
			return new static((string) $time);
		}
	}


	/**
	 * Creates DateTime object.
	 * @throws Nette\InvalidArgumentException if the date and time are not valid.
	 */
	public static function fromParts(
		int $year,
		int $month,
		int $day,
		int $hour = 0,
		int $minute = 0,
		float $second = 0.0,
	): static
	{
		$s = sprintf('%04d-%02d-%02d %02d:%02d:%02.5F', $year, $month, $day, $hour, $minute, $second);
		if (
			!checkdate($month, $day, $year)
			|| $hour < 0 || $hour > 23
			|| $minute < 0 || $minute > 59
			|| $second < 0 || $second >= 60
		) {
			throw new Nette\InvalidArgumentException("Invalid date '$s'");
		}

		return new static($s);
	}


	/**
	 * Returns a new DateTime object formatted according to the specified format.
	 */
	public static function createFromFormat(
		string $format,
		string $datetime,
		string|\DateTimeZone|null $timezone = null,
	): static|false
	{
		if ($timezone === null) {
			$timezone = new \DateTimeZone(date_default_timezone_get());

		} elseif (is_string($timezone)) {
			$timezone = new \DateTimeZone($timezone);
		}

		$date = parent::createFromFormat($format, $datetime, $timezone);
		return $date ? static::from($date) : false;
	}


	public function __construct(string $datetime = 'now', ?\DateTimeZone $timezone = null)
	{
		$this->apply($datetime, $timezone, true);
	}


	public function modify(string $modifier): static
	{
		$this->apply($modifier);
		return $this;
	}


	public function setDate(int $year, int $month, int $day): static
	{
		if (!checkdate($month, $day, $year)) {
			trigger_error(sprintf(self::class . ': The date %04d-%02d-%02d is not valid.', $year, $month, $day), E_USER_WARNING);
		}
		return parent::setDate($year, $month, $day);
	}


	public function setTime(int $hour, int $minute, int $second = 0, int $microsecond = 0): static
	{
		if (
			$hour < 0 || $hour > 23
			|| $minute < 0 || $minute > 59
			|| $second < 0 || $second >= 60
			|| $microsecond < 0 || $microsecond >= 1_000_000
		) {
			trigger_error(sprintf(self::class . ': The time %02d:%02d:%08.5F is not valid.', $hour, $minute, $second + $microsecond / 1_000_000), E_USER_WARNING);
		}
		return parent::setTime($hour, $minute, $second, $microsecond);
	}


	/**
	 * Converts a relative time string (e.g. '10 minut') to seconds.
	 */
	public static function relativeToSeconds(string $relativeTime): int
	{
		return (new \DateTimeImmutable('1970-01-01 ' . $relativeTime, new \DateTimeZone('UTC')))
			->getTimestamp();
	}


	private function apply(string $datetime, $timezone = null, bool $ctr = false): void
	{
		$relPart = '';
		$absPart = preg_replace_callback(
			'/[+-]?\s*\d+\s+((microsecond|millisecond|[mµu]sec)s?|[mµ]s|sec(ond)?s?|min(ute)?s?|hours?)\b/iu',
			function ($m) use (&$relPart) {
				$relPart .= $m[0] . ' ';
				return '';
			},
			$datetime,
		);

		if ($ctr) {
			parent::__construct($absPart, $timezone);
			$this->handleErrors($datetime);
		} elseif (trim($absPart)) {
			parent::modify($absPart) && $this->handleErrors($datetime);
		}

		if ($relPart) {
			$timezone ??= $this->getTimezone();
			$this->setTimezone(new \DateTimeZone('UTC'));
			parent::modify($relPart) && $this->handleErrors($datetime);
			$this->setTimezone($timezone);
		}
	}


	/**
	 * Returns JSON representation in ISO 8601 (used by JavaScript).
	 */
	public function jsonSerialize(): string
	{
		return $this->format('c');
	}


	/**
	 * Returns the date and time in the format 'Y-m-d H:i:s'.
	 */
	public function __toString(): string
	{
		return $this->format('Y-m-d H:i:s');
	}


	/**
	 * You'd better use: (clone $dt)->modify(...)
	 */
	public function modifyClone(string $modify = ''): static
	{
		$dolly = clone $this;
		return $modify ? $dolly->modify($modify) : $dolly;
	}


	private function handleErrors(string $value): void
	{
		$errors = self::getLastErrors();
		$errors = array_merge($errors['errors'] ?? [], $errors['warnings'] ?? []);
		if ($errors) {
			trigger_error(self::class . ': ' . implode(', ', $errors) . " '$value'", E_USER_WARNING);
		}
	}
}
