<?php

declare(strict_types=1);

// phpcs:ignoreFile

const MENU_IS_ROOT = 0x0001;

const MENU_VISIBLE_IN_TREE = 0x0002;

const MENU_VISIBLE_IN_BREADCRUMB = 0x0004;

const MENU_LINKS_TO_PARENT = 0x0008;

const MENU_MODIFIED_BY_ADMIN = 0x0020;

const MENU_CREATED_BY_ADMIN = 0x0040;

const MENU_IS_LOCAL_TASK = 0x0080;

const MENU_IS_LOCAL_ACTION = 0x0100;

const MENU_NORMAL_ITEM = MENU_VISIBLE_IN_TREE | MENU_VISIBLE_IN_BREADCRUMB;

const MENU_CALLBACK = 0x0000;

const MENU_SUGGESTED_ITEM = MENU_VISIBLE_IN_BREADCRUMB | 0x0010;

const MENU_LOCAL_TASK = MENU_IS_LOCAL_TASK | MENU_VISIBLE_IN_BREADCRUMB;

const MENU_DEFAULT_LOCAL_TASK = MENU_IS_LOCAL_TASK | MENU_LINKS_TO_PARENT | MENU_VISIBLE_IN_BREADCRUMB;

const MENU_LOCAL_ACTION = MENU_IS_LOCAL_TASK | MENU_IS_LOCAL_ACTION | MENU_VISIBLE_IN_BREADCRUMB;

const DRUPAL_NO_CACHE = -1;

const DRUPAL_CACHE_CUSTOM = -2;

const DRUPAL_CACHE_PER_ROLE = 0x0001;

const DRUPAL_CACHE_PER_USER = 0x0002;

const DRUPAL_CACHE_PER_PAGE = 0x0004;

const DRUPAL_CACHE_GLOBAL = 0x0008;

const BLOCK_REGION_NONE = -1;

const BLOCK_CUSTOM_FIXED = 0;

const BLOCK_CUSTOM_ENABLED = 1;

const BLOCK_CUSTOM_DISABLED = 2;

const BLOCK_VISIBILITY_NOTLISTED = 0;

const BLOCK_VISIBILITY_LISTED = 1;

const BLOCK_VISIBILITY_PHP = 2;

require_once __DIR__ . '/constants/comment.php';
require_once __DIR__ . '/constants/field.php';
require_once __DIR__ . '/constants/file.php';
