    </main>

    <footer class="border-t border-slate-200 dark:border-emerald-800/40 bg-white dark:bg-emerald-950 py-6 text-center text-xs text-slate-500 dark:text-emerald-400/60">
        <p>Canlı Kur'an-ı Kerim Radyo & Tilavet Portalı - Yönetim Sistemi &copy; <?= date('Y') ?></p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeBtn = document.getElementById('adminThemeToggleBtn');
            const htmlEl = document.documentElement;

            function updateThemeIcon() {
                if (!themeBtn) return;
                const icon = themeBtn.querySelector('i');
                if (!icon) return;
                if (htmlEl.classList.contains('dark')) {
                    icon.className = 'fas fa-sun text-amber-400';
                } else {
                    icon.className = 'fas fa-moon text-slate-700';
                }
            }

            updateThemeIcon();

            if (themeBtn) {
                themeBtn.addEventListener('click', () => {
                    if (htmlEl.classList.contains('dark')) {
                        htmlEl.classList.remove('dark');
                        localStorage.setItem('quran_admin_theme', 'light');
                    } else {
                        htmlEl.classList.add('dark');
                        localStorage.setItem('quran_admin_theme', 'dark');
                    }
                    updateThemeIcon();
                });
            }
        });
    </script>
</body>
</html>
