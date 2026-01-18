<?php

use PHPUnit\Framework\TestCase;

class LayoutEscapingTest extends TestCase
{
    public function testLayoutEscapesMenuKeyAndLabel(): void
    {
        $view = file_get_contents(__DIR__ . '/../public/views/layout.php');
        $this->assertStringContainsString("htmlspecialchars(", $view, 'Layout should use htmlspecialchars for user-visible labels');
        $this->assertStringContainsString("rawurlencode(", $view, 'Layout should rawurlencode page keys when used in href');
    }
}
