<?php

namespace App\Enums;

// This enum is the SOURCE OF TRUTH for state of permissions in the db
// - Removing a permission from this enum will delete it from the database (when this seeder is ran)
// - Adding a permission here will create it if it does not exist (when this seeder is ran)
enum PermissionType: string
{
    case NO_RESTRICTION = 'no-restriction';
    case MANAGE_USERS = 'manage-users';
    case MANAGE_STUDIES = 'manage-studies';
    case MANAGE_FACILITIES = 'manage-facilities';
    case MANAGE_FEEDBACK = 'manage-feedback';
    case MANAGE_COLLECTIONS = 'manage-collections';
    case VIEW_OWN_COLLECTIONS = 'view-own-collections';
    case EDIT_OWN_COLLECTIONS = 'edit-own-collections';
    case SUBMIT_OWN_COLLECTIONS = 'submit-own-collections';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function description(PermissionType $permission): ?string
    {
        return match ($permission) {
            self::NO_RESTRICTION => 'Unrestricted access to all permissions',
            self::MANAGE_USERS => null,
            self::MANAGE_STUDIES => null,
            self::MANAGE_FACILITIES => null,
            self::MANAGE_FEEDBACK => null,
            self::MANAGE_COLLECTIONS => 'Access to create and manage bulk data collections',
            self::VIEW_OWN_COLLECTIONS => 'View assigned collections where you are the corresponding investigator',
            self::EDIT_OWN_COLLECTIONS => 'Edit assigned collection metadata where you are the corresponding investigator',
            self::SUBMIT_OWN_COLLECTIONS => 'Submit assigned collections for ROSSA review where you are the corresponding investigator',
        };
    }
}
