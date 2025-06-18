let timeout = 300; // 5 minutes
let timer = setTimeout(() => {
    window.location.href = 'logout.php?timeout=1';
}, timeout);

document.onmousemove = document.keydown = () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        window.location.href = 'logout.php?timeout=1';
    }, timeout);
};