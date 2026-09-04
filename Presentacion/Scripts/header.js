document.addEventListener('DOMContentLoaded', () => {
    const DEFAULT_AVATAR = '../../Assets/DiogenesPerro.png';
    const headerAvatarImg = document.getElementById('header-avatar-img');
    const activeAvatar = localStorage.getItem('userAvatar') || DEFAULT_AVATAR;

    if (headerAvatarImg) {
        headerAvatarImg.style.backgroundImage = `url('${activeAvatar}')`;
    }
});