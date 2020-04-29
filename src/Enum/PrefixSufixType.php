<?php

namespace App\Enum;

use App\Enum\Base\Enum;

abstract class PrefixSufixType extends Enum
{
    const CustomText = 'custom-text';

    const FileName = 'file-name';

    const CategoryName = 'category-name';

    const NodeName = 'node-name';

    const NodeSymbol = 'node-symbol';

    public static function getData()
    {
        return [
            self::CustomText => 'Custom text',
            self::FileName => 'File name',
            self::CategoryName => 'Category name',
            self::NodeName => 'Node title',
            self::NodeSymbol => 'Node symbol'
        ];
    }
}
