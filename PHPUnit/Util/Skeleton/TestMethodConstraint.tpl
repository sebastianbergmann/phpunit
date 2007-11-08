
    /**
     * Generated from @assert {annotation}.
     */
    public function test{methodName}() {
        $constraint = $this->{constraint}({expected});
        $object     = new {class};
        $value      = $object->{origMethodName}({arguments});
        $this->assertThat($value, $constraint);
    }
