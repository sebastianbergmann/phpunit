
    {modifier} function {reference}{method_name}({arguments_decl}){return_declaration}
    {{deprecation}
        $definedVariables = get_defined_vars();
        $namedVariadicParameters = [];
        foreach ($definedVariables as $name => $value) {
            $reflectionParam = new ReflectionParameter([__CLASS__, __FUNCTION__], $name);
            if ($reflectionParam->isVariadic()) {
                foreach ($value as $key => $namedValue) {
                    if (is_string($key)) {
                        $namedVariadicParameters[$key] = $namedValue;
                    }
                }
            }
        }
        $__phpunit_arguments = [{arguments_call}];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > {arguments_count}) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = {arguments_count}; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }
        $__phpunit_arguments = array_merge($__phpunit_arguments, $namedVariadicParameters);

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                '{class_name}', '{method_name}', $__phpunit_arguments, '{return_type}', $this, {clone_arguments}
            )
        );{return_result}
    }
