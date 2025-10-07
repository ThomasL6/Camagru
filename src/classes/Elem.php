<?php
class Elem {
    private $tag;
    private $attributes;
    private $children;
    private $selfClosing;

    public function __construct($tag, $attributes = []) {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->children = [];
        $this->selfClosing = in_array($tag, ['img', 'input', 'br', 'hr', 'meta', 'link']);
    }

    public function addChild($child) {
        if (is_string($child) || (is_object($child) && method_exists($child, 'render'))) {
            $this->children[] = $child;
        }
        return $this;
    }

    public function render() {
        $html = '<' . $this->tag;
        
        foreach ($this->attributes as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        
        if ($this->selfClosing) {
            $html .= ' />';
        } else {
            $html .= '>';
            
            foreach ($this->children as $child) {
                if (is_string($child)) {
                    $html .= $child;
                } else {
                    $html .= $child->render();
                }
            }
            
            $html .= '</' . $this->tag . '>';
        }
        
        return $html;
    }
}
?>