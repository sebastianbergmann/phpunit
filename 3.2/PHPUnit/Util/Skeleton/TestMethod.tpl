
    /**
     * Generated from @assert {annotation}.
     */
    public function test{methodName}() {
        $object = new {class};
        $this->assert{assertion}(
          {expected},
          $object->{origMethodName}({arguments})
        );
    }
