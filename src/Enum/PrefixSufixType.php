<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class PrefixSufixType extends Enum
{
    const CustomText = 'custom-text';

    const FileDescription = 'file-description';

    const CategoryName = 'category-name';

    const NodeName = 'node-name';

    const NodeSymbol = 'node-symbol';

    public static function getData()
    {
        return [
            self::CustomText => 'Custom text',
            self::FileDescription => 'File description',
            self::CategoryName => 'Category name',
            self::NodeName => 'Node title',
            self::NodeSymbol => 'Node symbol'
        ];
    }
}
