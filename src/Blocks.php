<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class BlockHelpers
{
    public static function register(string $name, array $args = []): void
    {
        register_block_type($name, $args);
    }
    
    public static function parse(string $content): array
    {
        return parse_blocks($content);
    }
    
    public static function render(string $blockName, array $attributes = [], string $content = ''): string
    {
        $block = [
            'blockName' => $blockName,
            'attrs' => $attributes,
            'innerContent' => [$content],
        ];
        
        return render_block($block);
    }
}

function registerBlock(string $name, array $args = []): void
{
    BlockHelpers::register($name, $args);
}

function parseBlocks(string $content): array
{
    return BlockHelpers::parse($content);
}

function renderBlock(string $blockName, array $attributes = [], string $content = ''): string
{
    return BlockHelpers::render($blockName, $attributes, $content);
}

function assertBlockRegistered(string $blockName): void
{
    $registry = \WP_Block_Type_Registry::get_instance();
    test()->assertTrue(
        $registry->is_registered($blockName),
        "Block '{$blockName}' is not registered"
    );
}

function assertBlockNotRegistered(string $blockName): void
{
    $registry = \WP_Block_Type_Registry::get_instance();
    test()->assertFalse(
        $registry->is_registered($blockName),
        "Block '{$blockName}' should not be registered"
    );
}

function assertHasBlock(string $blockName, string $content): void
{
    test()->assertTrue(
        has_block($blockName, $content),
        "Content does not contain block '{$blockName}'"
    );
}

function assertBlockCount(int $count, string $content): void
{
    $blocks = parse_blocks($content);
    $actualCount = count(array_filter($blocks, fn($block) => !empty($block['blockName'])));
    
    test()->assertEquals($count, $actualCount, "Expected {$count} blocks, found {$actualCount}");
}


