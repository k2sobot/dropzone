<?php $__env->startSection('content'); ?>
<div class="max-w-md w-full">
    <div class="bg-gray-800/80 backdrop-blur rounded-lg p-8 shadow-xl">
        <h1 class="text-2xl font-bold text-white text-center mb-6"><?php echo e($siteName ?? 'Dropzone'); ?></h1>

        <form action="<?php echo e(route('upload.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center hover:border-blue-500 transition-colors cursor-pointer" onclick="document.getElementById('file-input').click()">
                <input type="file" name="file" id="file-input" class="hidden" required onchange="updateFileName(this)">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 10h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-sm text-gray-400">Click to upload or drag and drop</p>
                <p class="mt-1 text-xs text-gray-500">Max file size: <?php echo e($maxFileSize ?? 100); ?>MB</p>
                <p id="file-name" class="mt-2 text-sm text-blue-400 hidden"></p>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                Upload File
            </button>
        </form>

        <?php if($siteName ?? null): ?>
        <p class="mt-4 text-center text-gray-500 text-sm">
            <a href="<?php echo e(route('admin.login')); ?>" class="hover:text-gray-300">Admin</a>
        </p>
        <?php endif; ?>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileName = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        fileName.textContent = input.files[0].name;
        fileName.classList.remove('hidden');
    }
}

// Drag and drop support
const dropZone = document.querySelector('.border-dashed');

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'bg-blue-500/10');
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-500/10');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'bg-blue-500/10');

    const files = e.dataTransfer.files;
    if (files.length) {
        document.getElementById('file-input').files = files;
        updateFileName(document.getElementById('file-input'));
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/upload-content.blade.php ENDPATH**/ ?>