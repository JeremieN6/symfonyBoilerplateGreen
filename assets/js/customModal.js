document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('uc-contact-modal');
    const closeModalBtn = document.getElementById('close-modal');
    const video = document.getElementById('demo-video');

    if (modal && closeModalBtn && video) { // Vérifiez si les éléments existent
        function stopVideo() {
            video.pause();
            video.currentTime = 0;
        }

        closeModalBtn.addEventListener('click', stopVideo);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                stopVideo();
            }
        });
    } else {
        console.error('Un ou plusieurs éléments sont introuvables dans le DOM.');
    }
});