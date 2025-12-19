<?php

declare(strict_types=1);

namespace PestPluginWordPress;

class WidgetHelpers
{
    public static function register(string $widgetClass): void
    {
        register_widget($widgetClass);
    }
    
    public static function addToSidebar(string $sidebar, string $widget, array $instance = []): void
    {
        global $wp_registered_sidebars, $wp_registered_widgets;
        
        $sidebars_widgets = wp_get_sidebars_widgets();
        
        if (!isset($sidebars_widgets[$sidebar])) {
            $sidebars_widgets[$sidebar] = [];
        }
        
        $widgetOptions = get_option("widget_{$widget}", []);
        
        // Ensure widget options array has the _multiwidget flag
        if (!isset($widgetOptions['_multiwidget'])) {
            $widgetOptions['_multiwidget'] = 1;
        }
        
        $nextId = 2;
        while (isset($widgetOptions[$nextId])) {
            $nextId++;
        }
        
        $widgetId = $widget . '-' . $nextId;
        $sidebars_widgets[$sidebar][] = $widgetId;
        
        wp_set_sidebars_widgets($sidebars_widgets);
        
        $widgetOptions[(int)$nextId] = $instance;
        update_option("widget_{$widget}", $widgetOptions);
    }
}

function registerWidget(string $widgetClass): void
{
    WidgetHelpers::register($widgetClass);
}

function addWidgetToSidebar(string $sidebar, string $widget, array $instance = []): void
{
    WidgetHelpers::addToSidebar($sidebar, $widget, $instance);
}

function assertWidgetRegistered(string $widgetClass): void
{
    global $wp_widget_factory;
    
    $registered = false;
    foreach ($wp_widget_factory->widgets as $widget) {
        if (get_class($widget) === $widgetClass) {
            $registered = true;
            break;
        }
    }
    
    test()->assertTrue($registered, "Widget '{$widgetClass}' is not registered");
}

function assertSidebarExists(string $sidebar): void
{
    global $wp_registered_sidebars;
    test()->assertArrayHasKey($sidebar, $wp_registered_sidebars, "Sidebar '{$sidebar}' does not exist");
}

function assertSidebarHasWidgets(string $sidebar, int $count): void
{
    $sidebars = wp_get_sidebars_widgets();
    $widgets = $sidebars[$sidebar] ?? [];
    
    test()->assertCount($count, $widgets, "Expected {$count} widgets in sidebar '{$sidebar}'");
}

