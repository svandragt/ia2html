<?php

class Template
{

    protected $args;
    protected $file;

    public function __construct($file, $args = array())
    {
        $this->file = $file;
        $this->args = $args;
    }

    public function __get($name)
    {
        return $this->args[ $name ];
    }

    public function render(): void
    {
        require $this->file;
    }
}