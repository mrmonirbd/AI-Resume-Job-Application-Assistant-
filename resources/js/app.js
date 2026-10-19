import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.profilePhotoCropper = (existingPreview) => ({
    cropOpen: false,
    cropDragging: false,
    cropDragOriginX: 0,
    cropDragOriginY: 0,
    cropResizeOriginSize: 0,
    cropSize: 224,
    cropStartX: 0,
    cropStartY: 0,
    cropX: 32,
    cropY: 32,
    dragging: false,
    dragOriginX: 0,
    dragOriginY: 0,
    dragStartX: 0,
    dragStartY: 0,
    image: null,
    imageHeight: 0,
    imageWidth: 0,
    maxScale: 1,
    minScale: 1,
    previewUrl: existingPreview,
    scale: 1,
    sourceUrl: null,
    stageSize: 288,
    x: 0,
    y: 0,

    choosePhoto(event) {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        if (this.sourceUrl) {
            URL.revokeObjectURL(this.sourceUrl);
        }

        this.sourceUrl = URL.createObjectURL(file);
        this.cropOpen = true;

        const image = new Image();
        image.onload = () => {
            this.image = image;
            this.imageWidth = image.naturalWidth;
            this.imageHeight = image.naturalHeight;
            this.minScale = Math.max(this.stageSize / this.imageWidth, this.stageSize / this.imageHeight);
            this.maxScale = this.minScale * 3;
            this.scale = this.minScale;
            this.x = (this.stageSize - this.imageWidth * this.scale) / 2;
            this.y = (this.stageSize - this.imageHeight * this.scale) / 2;
            this.cropSize = 224;
            this.cropX = (this.stageSize - this.cropSize) / 2;
            this.cropY = (this.stageSize - this.cropSize) / 2;
        };
        image.src = this.sourceUrl;
    },

    clampPosition() {
        const visibleWidth = this.imageWidth * this.scale;
        const visibleHeight = this.imageHeight * this.scale;

        this.x = Math.min(0, Math.max(this.stageSize - visibleWidth, this.x));
        this.y = Math.min(0, Math.max(this.stageSize - visibleHeight, this.y));
    },

    clampCrop() {
        this.cropSize = Math.min(this.stageSize, Math.max(96, this.cropSize));
        this.cropX = Math.min(this.stageSize - this.cropSize, Math.max(0, this.cropX));
        this.cropY = Math.min(this.stageSize - this.cropSize, Math.max(0, this.cropY));
    },

    startDrag(event) {
        this.dragging = true;
        this.dragStartX = event.clientX;
        this.dragStartY = event.clientY;
        this.dragOriginX = this.x;
        this.dragOriginY = this.y;
        event.currentTarget.setPointerCapture(event.pointerId);
    },

    drag(event) {
        if (!this.dragging) {
            return;
        }

        this.x = this.dragOriginX + event.clientX - this.dragStartX;
        this.y = this.dragOriginY + event.clientY - this.dragStartY;
        this.clampPosition();
    },

    stopDrag(event) {
        this.dragging = false;

        if (event.currentTarget.hasPointerCapture(event.pointerId)) {
            event.currentTarget.releasePointerCapture(event.pointerId);
        }
    },

    startCropMove(event) {
        this.cropDragging = true;
        this.cropStartX = event.clientX;
        this.cropStartY = event.clientY;
        this.cropDragOriginX = this.cropX;
        this.cropDragOriginY = this.cropY;
        event.currentTarget.setPointerCapture(event.pointerId);
    },

    moveCrop(event) {
        if (!this.cropDragging) {
            return;
        }

        this.cropX = this.cropDragOriginX + event.clientX - this.cropStartX;
        this.cropY = this.cropDragOriginY + event.clientY - this.cropStartY;
        this.clampCrop();
    },

    startCropResize(event) {
        this.cropDragging = false;
        this.cropStartX = event.clientX;
        this.cropStartY = event.clientY;
        this.cropResizeOriginSize = this.cropSize;
        event.currentTarget.setPointerCapture(event.pointerId);
    },

    resizeCrop(event) {
        if (!event.currentTarget.hasPointerCapture(event.pointerId)) {
            return;
        }

        const resizeOffset = Math.max(event.clientX - this.cropStartX, event.clientY - this.cropStartY);
        this.cropSize = this.cropResizeOriginSize + resizeOffset;
        this.cropSize = Math.min(this.cropSize, this.stageSize - this.cropX, this.stageSize - this.cropY);
        this.clampCrop();
    },

    stopCropPointer(event) {
        this.cropDragging = false;

        if (event.currentTarget.hasPointerCapture(event.pointerId)) {
            event.currentTarget.releasePointerCapture(event.pointerId);
        }
    },

    async useCroppedPhoto() {
        if (!this.image) {
            return;
        }

        const cropSize = this.cropSize / this.scale;
        const sourceX = (this.cropX - this.x) / this.scale;
        const sourceY = (this.cropY - this.y) / this.scale;
        const canvas = this.$refs.cropCanvas;
        canvas.width = 600;
        canvas.height = 600;
        const context = canvas.getContext('2d');
        context.drawImage(this.image, sourceX, sourceY, cropSize, cropSize, 0, 0, canvas.width, canvas.height);

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.9));

        if (!blob) {
            return;
        }

        const photo = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' });
        const transfer = new DataTransfer();
        transfer.items.add(photo);
        this.$refs.profilePhoto.files = transfer.files;
        this.previewUrl = URL.createObjectURL(photo);
        this.cropOpen = false;
    },

    cancelCrop() {
        this.$refs.profilePhoto.value = '';
        this.cropOpen = false;
    },
});

Alpine.start();
