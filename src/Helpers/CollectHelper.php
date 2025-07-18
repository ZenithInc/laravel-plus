<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Helpers;

class CollectHelper
{
    /**
     * Takes an array of items and remaps a specific attribute for each item using a provided map.
     *
     * This method iterates over a provided array of items. For each item, it uses the provided map to alter the value
     * of a specific attribute named in the $attributeName param. If the map does not contain a corresponding value,
     * it uses the provided $defaultValue.
     *
     * @param  array  $items  An array of items to be processed.
     * @param  array  $map  An associative array used for mapping attribute values.
     * @param  string  $attributeName  The name of the attribute to be mapped.
     * @param  string  $mapAttributeName  The attribute name to be used as key in the map array.
     * @param  mixed  $defaultValue  The default value to be used when the map does not contain a corresponding value.
     * @return array An array of items with the specified attribute remapped.
     */
    public static function mapAttributeInItems(array $items, array $map, string $attributeName, string $mapAttributeName, mixed $defaultValue = ''): array
    {
        return collect($items)->map(function ($item) use ($attributeName, $mapAttributeName, $defaultValue, $map) {
            if (! isset($item[$mapAttributeName])) {
                $item[$attributeName] = $defaultValue;
            } else {
                $item[$attributeName] = $map[$item[$mapAttributeName]] ?? $defaultValue;
            }

            return $item;
        })->all();
    }

    /**
     * Extracts a specific column from an array of items.
     *
     * This method takes an array of items and extracts the values of a specific column.
     * to retrieve the values and returns them as an array.
     *
     * @param  array  $items  An array of items to extract the column from.
     * @param  string  $column  The name of the column to extract.
     * @return array An array containing the values of the specified column.
     */
    public static function column(array $items, string $column): array
    {
        return collect($items)->pluck($column)->unique()->values()->toArray();
    }

    /**
     * Extracts specified columns from an array of items and returns them as a flat array.
     *
     * This method takes an array of items and an array of columns. It iterates over each item in the array and extracts
     * the values of the specified columns, creating a flat array of these values.
     *
     * @param  array  $items  An array of items to extract columns from.
     * @param  array  $columns  An array of column names to extract values from.
     * @return array A flat array containing the extracted values from the specified columns.
     */
    public static function extractColumnsToArray(array $items, array $columns): array
    {
        return collect($items)->flatMap(function ($item) use ($columns) {
            $elements = [];
            foreach ($columns as $column) {
                $elements[] = $item[$column];
            }

            return $elements;
        })->filter(fn ($item) => !empty($item))->unique()->values()->toArray();
    }

    /**
     * Build a tree structure from an array of items.
     *
     * @param array $items The array of items to build the tree from.
     * @param string $parentKey The key for the parent identifier in each item. Default is 'parent_id'.
     * @param string $uniqueKey The key for the unique identifier in each item. Default is 'id'.
     * @param string $childrenKey The key for the children array in each item. Default is 'children'.
     * @return array The tree structure built from the array of items.
     */
    public static function buildTree(array $items, string $parentKey = 'parent_id', string $uniqueKey = 'id', string $childrenKey = 'children'): array
    {
        $tree = [];
        $items = collect($items)->keyBy($uniqueKey)->toArray();
        foreach ($items as $item) {
            if ($item[$parentKey] !== 0) {
                $items[$item[$parentKey]][$childrenKey][] = &$items[$item[$uniqueKey]];
            } else {
                $tree[] = &$items[$item[$uniqueKey]];
            }
        }

        return $tree;
    }
}
