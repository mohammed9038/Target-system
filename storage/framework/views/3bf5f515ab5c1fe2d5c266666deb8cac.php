<?php $__env->startSection('title', __('Edit User')); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h2 mb-1"><?php echo e(__('Edit User')); ?></h1>
        <p class="text-muted mb-0"><?php echo e(__('Update user account information')); ?></p>
    </div>
    <div class="d-flex gap-2" style="margin-top: 0.5rem;">
        <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-outline-info">
            <i class="bi bi-eye me-2"></i><?php echo e(__('View User')); ?>

        </a>
        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i><?php echo e(__('Back to Users')); ?>

        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-gear me-2"></i><?php echo e(__('User Information')); ?>

                </h5>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('users.update', $user)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-medium">
                                    <i class="bi bi-person me-1"></i><?php echo e(__('Username')); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo e(old('username', $user->username)); ?>" 
                                       placeholder="<?php echo e(__('Enter username')); ?>"
                                       required>
                                <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label fw-medium">
                                    <i class="bi bi-shield me-1"></i><?php echo e(__('Role')); ?> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="role" name="role" required>
                                    <option value=""><?php echo e(__('Select Role')); ?></option>
                                    <option value="admin" <?php echo e(old('role', $user->role) === 'admin' ? 'selected' : ''); ?>>
                                        <?php echo e(__('Admin')); ?> - <?php echo e(__('Full system access')); ?>

                                    </option>
                                    <option value="manager" <?php echo e(old('role', $user->role) === 'manager' ? 'selected' : ''); ?>>
                                        <?php echo e(__('Manager')); ?> - <?php echo e(__('Limited to assigned region/channel')); ?>

                                    </option>
                                </select>
                                <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">
                                    <i class="bi bi-lock me-1"></i><?php echo e(__('New Password')); ?>

                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="password" 
                                           name="password" 
                                           placeholder="<?php echo e(__('Leave blank to keep current password')); ?>">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                                        <i class="bi bi-eye" id="password-icon"></i>
                                    </button>
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <small class="text-muted"><?php echo e(__('Minimum 6 characters. Leave blank to keep current password.')); ?></small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-medium">
                                    <i class="bi bi-lock-fill me-1"></i><?php echo e(__('Confirm New Password')); ?>

                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="<?php echo e(__('Confirm new password')); ?>">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation')">
                                        <i class="bi bi-eye" id="password_confirmation-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="scope-section" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong><?php echo e(__('Manager Scope')); ?></strong><br>
                                <?php echo e(__('Managers can only see and manage data within their assigned regions and channels. Select multiple regions/channels or leave blank for access to all.')); ?>

                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-geo-alt me-1"></i><?php echo e(__('Regions')); ?>

                                </label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="all_regions" onclick="toggleAllRegions()">
                                        <label class="form-check-label fw-medium text-primary" for="all_regions">
                                            <?php echo e(__('All Regions')); ?>

                                        </label>
                                    </div>
                                    <hr class="my-2">
                                    <?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $userRegionIds = old('region_ids', $user->regions->pluck('id')->toArray());
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input region-checkbox" type="checkbox" 
                                                   name="region_ids[]" value="<?php echo e($region->id); ?>" 
                                                   id="region_<?php echo e($region->id); ?>"
                                                   <?php echo e(in_array($region->id, $userRegionIds) ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="region_<?php echo e($region->id); ?>">
                                                <?php echo e($region->name); ?> <small class="text-muted">(<?php echo e($region->region_code); ?>)</small>
                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php $__errorArgs = ['region_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['region_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-diagram-3 me-1"></i><?php echo e(__('Channels')); ?>

                                </label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="all_channels" onclick="toggleAllChannels()">
                                        <label class="form-check-label fw-medium text-primary" for="all_channels">
                                            <?php echo e(__('All Channels')); ?>

                                        </label>
                                    </div>
                                    <hr class="my-2">
                                    <?php $__currentLoopData = $channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $userChannelIds = old('channel_ids', $user->channels->pluck('id')->toArray());
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input channel-checkbox" type="checkbox" 
                                                   name="channel_ids[]" value="<?php echo e($channel->id); ?>" 
                                                   id="channel_<?php echo e($channel->id); ?>"
                                                   <?php echo e(in_array($channel->id, $userChannelIds) ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="channel_<?php echo e($channel->id); ?>">
                                                <?php echo e($channel->name); ?> <small class="text-muted">(<?php echo e($channel->channel_code); ?>)</small>
                                            </label>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php $__errorArgs = ['channel_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['channel_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="classification-section" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="bi bi-funnel me-2"></i>
                                <strong><?php echo e(__('Classification Filter')); ?></strong><br>
                                <?php echo e(__('Optionally restrict this manager to only see salesmen with a specific classification. Leave blank for access to all classifications.')); ?>

                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    <i class="bi bi-tags me-1"></i><?php echo e(__('Classifications')); ?>

                                </label>
                                <?php
                                    $userClassifications = old('classifications', $user->getClassificationListAttribute());
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input <?php $__errorArgs = ['classifications'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           type="checkbox" value="food" id="classification_food" 
                                           name="classifications[]" <?php echo e(in_array('food', $userClassifications) ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="classification_food">
                                        <?php echo e(__('Food')); ?>

                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input <?php $__errorArgs = ['classifications'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           type="checkbox" value="non_food" id="classification_non_food" 
                                           name="classifications[]" <?php echo e(in_array('non_food', $userClassifications) ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="classification_non_food">
                                        <?php echo e(__('Non-Food')); ?>

                                    </label>
                                </div>
                                <?php $__errorArgs = ['classifications'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i><?php echo e(__('Update User')); ?>

                        </button>
                        <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-outline-secondary">
                            <?php echo e(__('Cancel')); ?>

                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i><?php echo e(__('User Details')); ?>

                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted"><?php echo e(__('User ID')); ?></small>
                    <div class="fw-medium">#<?php echo e($user->id); ?></div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted"><?php echo e(__('Created')); ?></small>
                    <div class="fw-medium"><?php echo e($user->created_at->format('M d, Y H:i')); ?></div>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted"><?php echo e(__('Last Updated')); ?></small>
                    <div class="fw-medium"><?php echo e($user->updated_at->format('M d, Y H:i')); ?></div>
                </div>
                
                <?php if($user->id === auth()->id()): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small><strong><?php echo e(__('Note:')); ?></strong> <?php echo e(__('You are editing your own account.')); ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i><?php echo e(__('Role Permissions')); ?>

                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-danger">
                        <i class="bi bi-shield-fill me-1"></i><?php echo e(__('Admin')); ?>

                    </h6>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="bi bi-check text-success me-1"></i><?php echo e(__('Manage all master data')); ?></li>
                        <li><i class="bi bi-check text-success me-1"></i><?php echo e(__('Create/edit users')); ?></li>
                        <li><i class="bi bi-check text-success me-1"></i><?php echo e(__('Access all regions/channels')); ?></li>
                        <li><i class="bi bi-check text-success me-1"></i><?php echo e(__('Manage periods')); ?></li>
                        <li><i class="bi bi-check text-success me-1"></i><?php echo e(__('Full system access')); ?></li>
                    </ul>
                </div>
                
                <div>
                    <h6 class="text-info">
                        <i class="bi bi-person-check me-1"></i><?php echo e(__('Manager')); ?>

                    </h6>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="bi bi-check text-success me-1"></i><?php echo e(__('Manage targets')); ?></li>
                        <li><i class="bi bi-check text-success me-1"></i><?php echo e(__('View reports')); ?></li>
                        <li><i class="bi bi-x text-danger me-1"></i><?php echo e(__('Limited to assigned scope')); ?></li>
                        <li><i class="bi bi-x text-danger me-1"></i><?php echo e(__('Cannot manage master data')); ?></li>
                        <li><i class="bi bi-x text-danger me-1"></i><?php echo e(__('Cannot create users')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const scopeSection = document.getElementById('scope-section');
    const classificationSection = document.getElementById('classification-section');
    
    function toggleScopeSection() {
        if (roleSelect.value === 'manager') {
            scopeSection.style.display = 'block';
            classificationSection.style.display = 'block';
        } else {
            scopeSection.style.display = 'none';
            classificationSection.style.display = 'none';
        }
    }
    
    roleSelect.addEventListener('change', toggleScopeSection);
    toggleScopeSection(); // Initial check
});

function toggleAllRegions() {
    const allRegionsCheckbox = document.getElementById('all_regions');
    const regionCheckboxes = document.querySelectorAll('.region-checkbox');
    
    regionCheckboxes.forEach(checkbox => {
        checkbox.checked = allRegionsCheckbox.checked;
    });
}

function toggleAllChannels() {
    const allChannelsCheckbox = document.getElementById('all_channels');
    const channelCheckboxes = document.querySelectorAll('.channel-checkbox');
    
    channelCheckboxes.forEach(checkbox => {
        checkbox.checked = allChannelsCheckbox.checked;
    });
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Target\resources\views/users/edit.blade.php ENDPATH**/ ?>