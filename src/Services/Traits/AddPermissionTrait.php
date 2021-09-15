<?php

namespace Bytes\DiscordClientBundle\Services\Traits;

use Bytes\DiscordResponseBundle\Enums\ApplicationCommandPermissionType;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandPermission;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 */
trait AddPermissionTrait
{
    /**
     * Build a list of permissions to use with the editCommandPermissions method
     * @param ArrayCollection $permissions
     * @param string $role
     * @param ApplicationCommandPermissionType|null $type
     *
     * @return ArrayCollection
     *
     * @see \Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient::editCommandPermissions()
     */
    private function addPermission(ArrayCollection $permissions, string $role, ?ApplicationCommandPermissionType $type = null): ArrayCollection
    {
        $permission = ApplicationCommandPermission::create($role, $type ?? ApplicationCommandPermissionType::role(), true);
        if (!$permissions->exists(function ($key, $element) use ($permission) {
            return $element->getId() === $permission->getId() && $element->getType() === $permission->getType() && $element->getPermission() === $permission->getPermission();
        })) {
            $permissions->add($permission);
        }

        return $permissions;
    }
}