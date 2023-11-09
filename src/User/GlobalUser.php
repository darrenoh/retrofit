<?php

declare(strict_types=1);

namespace Retrofit\Drupal\User;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\Role;

final class GlobalUser implements AccountInterface
{
    public function __construct(
        private readonly AccountInterface $inner
    ) {
    }

    public function id()
    {
        return $this->inner->id();
    }

    public function getRoles($exclude_locked_roles = false)
    {
        $roles = $this->inner->getRoles($exclude_locked_roles);
        // Drupal 7 appended `user` to locked roles, so we need to do the same.
        if (!$exclude_locked_roles) {
            if ($this->isAuthenticated()) {
                $roles[] = 'authenticated user';
            } else {
                $roles[] = 'anonymous user';
            }
        }
        return $roles;
    }

    public function hasPermission($permission)
    {
        return $this->inner->hasPermission($permission);
    }

    public function isAuthenticated()
    {
        return $this->inner->isAuthenticated();
    }

    public function isAnonymous()
    {
        return $this->inner->isAnonymous();
    }

    public function getPreferredLangcode($fallback_to_default = true)
    {
        return $this->inner->getPreferredLangcode($fallback_to_default);
    }

    public function getPreferredAdminLangcode($fallback_to_default = true)
    {
        return $this->inner->getPreferredAdminLangcode($fallback_to_default);
    }

    public function getAccountName()
    {
        return $this->inner->getAccountName();
    }

    public function getDisplayName()
    {
        return $this->inner->getDisplayName();
    }

    public function getEmail()
    {
        return $this->inner->getEmail();
    }

    public function getTimeZone()
    {
        return $this->inner->getTimeZone();
    }

    public function getLastAccessedTime()
    {
        return $this->inner->getLastAccessedTime();
    }

    public function __get(string $name)
    {
        return match ($name) {
            'roles' => array_combine($this->getRoles(), array_map([Role::class, 'load'], $this->getRoles())),
            'name' => $this->getAccountName(),
            default => null,
        };
    }

    public function __set(string $name, $value): void
    {
        $this->inner->$name = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->inner->$name);
    }
}
