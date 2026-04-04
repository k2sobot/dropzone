<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($siteName ?? 'Dropzone'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-custom {
            <?php if($backgroundImage ?? null): ?>
                background-image: url('<?php echo e($backgroundImage); ?>');
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
            <?php endif; ?>
        }
    </style>
</head>
<body class="bg-custom min-h-screen bg-gray-900">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <?php if($errors->any()): ?>
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-200 max-w-md w-full">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-200 max-w-md w-full">
                <p><?php echo e(session('success')); ?></p>
                <?php if(session('download_url')): ?>
                    <div class="mt-3">
                        <input type="text" value="<?php echo e(session('download_url')); ?>" readonly
                            class="w-full bg-gray-800 text-white p-2 rounded text-sm"
                            onclick="this.select(); navigator.clipboard.writeText(this.value);">
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-200 max-w-md w-full">
                <p><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>

        <footer class="mt-8 text-gray-400 text-sm">
            <a href="/" class="hover:text-white"><?php echo e($siteName ?? 'Dropzone'); ?></a>
        </footer>
    </div>
</body>
</html>
<?php /**PATH /var/www/resources/views/layout.blade.php ENDPATH**/ ?>