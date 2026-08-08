<?php

return [
    // Home page
    [
        'pattern' => '',
        'controller' => 'HomeController',
        'action' => 'index'
    ],
    
    // Static pages
    [
        'pattern' => 'about',
        'controller' => 'PageController',
        'action' => 'about'
    ],
    [
        'pattern' => 'contact',
        'controller' => 'PageController',
        'action' => 'contact'
    ],
    
    // Auth
    [
        'pattern' => 'login',
        'controller' => 'AuthController',
        'action' => 'login'
    ],
    [
        'pattern' => 'register',
        'controller' => 'AuthController',
        'action' => 'register'
    ],
    [
        'pattern' => 'logout',
        'controller' => 'AuthController',
        'action' => 'logout'
    ],
    [
        'pattern' => 'forgot-password',
        'controller' => 'AuthController',
        'action' => 'forgot'
    ],
    [
        'pattern' => 'reset-password/{token}',
        'controller' => 'AuthController',
        'action' => 'reset'
    ],
    [
        'pattern' => 'verify/{token}',
        'controller' => 'AuthController',
        'action' => 'verify'
    ],
    
    // Posts
    [
        'pattern' => 'post/{slug}',
        'controller' => 'HomeController',
        'action' => 'post'
    ],
    [
        'pattern' => 'category/{slug}',
        'controller' => 'HomeController',
        'action' => 'category'
    ],
    
    // Software
    [
        'pattern' => 'software',
        'controller' => 'SoftwareController',
        'action' => 'index'
    ],
    
    // Admin
    [
        'pattern' => 'admin',
        'controller' => 'AdminController',
        'action' => 'dashboard'
    ],
    [
        'pattern' => 'admin/posts',
        'controller' => 'AdminController',
        'action' => 'posts'
    ],
    [
        'pattern' => 'admin/posts/create',
        'controller' => 'AdminController',
        'action' => 'createPost'
    ],
    [
        'pattern' => 'admin/posts/edit/{id}',
        'controller' => 'AdminController',
        'action' => 'editPost'
    ],
    [
        'pattern' => 'admin/posts/delete/{id}',
        'controller' => 'AdminController',
        'action' => 'deletePost'
    ],
    [
        'pattern' => 'admin/messages',
        'controller' => 'AdminController',
        'action' => 'messages'
    ],
    [
        'pattern' => 'admin/settings',
        'controller' => 'AdminController',
        'action' => 'settings'
    ],
    // Software routes
    [
        'pattern' => 'software',
        'controller' => 'SoftwareController',
        'action' => 'index'
    ],
    [
        'pattern' => 'software/{slug}',
        'controller' => 'SoftwareController',
        'action' => 'detail'
    ],
];