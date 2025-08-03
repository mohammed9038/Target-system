<?php return array (
  'App\\Providers\\EventServiceProvider' => 
  array (
    'Illuminate\\Auth\\Events\\Registered' => 
    array (
      0 => 'Illuminate\\Auth\\Listeners\\SendEmailVerificationNotification',
    ),
    'App\\Events\\TargetUpdated' => 
    array (
      0 => 'App\\Listeners\\ClearTargetCache',
    ),
  ),
  'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider' => 
  array (
    'App\\Events\\TargetUpdated' => 
    array (
      0 => 'App\\Listeners\\ClearTargetCache@handle',
    ),
  ),
);