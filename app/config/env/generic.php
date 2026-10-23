<?php
// On Symfony container compilation*, reads parameters from env variables if defined and overrides the yml parameters.
// * For typical use cases like Docker, make sure to recompile Symfony container on run to refresh settings.

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

// The SQLite database file (database_path) is passed to Doctrine as the connection's "path" for every
// driver (config.yml), and Doctrine's doctrine:database:create, which ezplatform:install runs first,
// takes "path" before "dbname". Keep it for pdo_sqlite only, so MySQL/MariaDB and PostgreSQL create
// and use the database named in database_name. DATABASE_PATH overrides the SQLite file.
if ($value = getenv('DATABASE_PATH')) {
    $container->setParameter('database_path', $value);
}
// At compile time the parameter holds an env placeholder; resolve it to the DATABASE_DRIVER
// environment variable, else the env(DATABASE_DRIVER) default of parameters.yml.
if (getenv('DATABASE_DRIVER') !== false || $container->hasParameter('env(DATABASE_DRIVER)')) {
    $databaseDriver = $container->resolveEnvPlaceholders('%env(DATABASE_DRIVER)%', true);
    if ($databaseDriver !== 'pdo_sqlite') {
        $container->setParameter('database_path', null);
    }
}

if ($dfsNfsPath = getenv('DFS_NFS_PATH')) {
    $container->setParameter('dfs_nfs_path', $dfsNfsPath);

    if ($value = getenv('DFS_DATABASE_DRIVER')) {
        $container->setParameter('dfs_database_driver', $value);
    } else {
        $container->setParameter('dfs_database_driver', $container->getParameter('database_driver'));
    }

    if ($value = getenv('DFS_DATABASE_HOST')) {
        $container->setParameter('dfs_database_host', $value);
    } else {
        $container->setParameter('dfs_database_host', $container->getParameter('database_host'));
    }

    if ($value = getenv('DFS_DATABASE_PORT')) {
        $container->setParameter('dfs_database_port', $value);
    } else {
        $container->setParameter('dfs_database_port', $container->getParameter('database_port'));
    }

    if ($value = getenv('DFS_DATABASE_NAME')) {
        $container->setParameter('dfs_database_name', $value);
    } else {
        $container->setParameter('dfs_database_name', $container->getParameter('database_name'));
    }

    if ($value = getenv('DFS_DATABASE_USER')) {
        $container->setParameter('dfs_database_user', $value);
    } else {
        $container->setParameter('dfs_database_user', $container->getParameter('database_user'));
    }

    if ($value = getenv('DFS_DATABASE_PASSWORD')) {
        $container->setParameter('dfs_database_password', $value);
    } else {
        $container->setParameter('dfs_database_password', $container->getParameter('database_password'));
    }

    $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../dfs'));
    $loader->load('dfs.yml');
}

// Cache settings
// If CACHE_POOL env variable is set, check if there is a yml file that needs to be loaded for it
if (($pool = getenv('CACHE_POOL')) && file_exists(__DIR__ . "/../cache_pool/${pool}.yml")) {
    $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../cache_pool'));
    $loader->load($pool . '.yml');
}

// Params that needs to be set at compile time and thus can't use Symfony's env()
if ($purgeType = getenv('HTTPCACHE_PURGE_TYPE')) {
    $container->setParameter('purge_type', $purgeType);
}

if ($value = getenv('MAILER_TRANSPORT')) {
    $container->setParameter('mailer_transport', $value);
}

if ($value = getenv('LOG_TYPE')) {
    $container->setParameter('log_type', $value);
}

if ($value = getenv('SESSION_HANDLER_ID')) {
    $container->setParameter('ezplatform.session.handler_id', $value);
}

if ($value = getenv('SESSION_SAVE_PATH')) {
    $container->setParameter('ezplatform.session.save_path', $value);
}
