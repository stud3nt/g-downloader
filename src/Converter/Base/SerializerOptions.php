<?php

namespace App\Converter\Base;

class SerializerOptions
{
    const GROUPS = 'groups';

    public $groups;

    public function groupEnable($checkedGroup): bool
    {
        if (empty($this->groups)) {
            return true;
        } elseif (is_array($this->groups)) {
            return in_array($checkedGroup, $this->groups);
        } elseif (is_string($checkedGroup)) {
            return $checkedGroup === $this->groups;
        }

        return false;
    }
}