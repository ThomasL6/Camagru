<?php

class Elem {
    public $tag;
    public $attributes = [];
    public $children = [];

    public function __construct($tag, $attributes = []) {
        $this->tag = $tag;
        $this->attributes = $attributes;
    }

    public function addChild($child) {
        $this->children[] = $child;
    }

    public function render() {
        $html = "<{$this->tag}";
        foreach ($this->attributes as $key => $value) {
            $html .= " $key=\"$value\"";
        }
        $html .= ">";
        foreach ($this->children as $child) {
            if (is_string($child)) {
                $html .= $child;
            } else {
                $html .= $child->render();
            }
        }
        $html .= "</{$this->tag}>";
        return $html;
    }
}
