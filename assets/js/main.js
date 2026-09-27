/**
 * Canlı Kur'an-ı Kerim Radyosu - Kullanıcı Arayüzü & Gelişmiş İnteraktif Kontroller
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initSearchAndFilter();
    initDailyAyahTicker();
    initEmbedModal();
    initSleepTimerModal();
    initPrayerTimesEngine();
    initSurahDirectory();
    initShareModal();
    initPwaInstallPrompt();
    registerServiceWorker();
});

/**
 * Gece / Gündüz Mode Toggle (Dark / Light Theme)
 */
function initThemeToggle() {
    const themeBtn = document.getElementById('themeToggleBtn');
    const htmlEl = document.documentElement;
    
    const savedTheme = localStorage.getItem('quran_theme');
    if (savedTheme === 'light') {
        htmlEl.classList.remove('dark');
        updateThemeIcon(false);
    } else {
        htmlEl.classList.add('dark');
        updateThemeIcon(true);
    }
    
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const isDark = htmlEl.classList.toggle('dark');
            localStorage.setItem('quran_theme', isDark ? 'dark' : 'light');
            updateThemeIcon(isDark);
        });
    }
}

function updateThemeIcon(isDark) {
    const icon = document.querySelector('#themeToggleBtn i');
    if (icon) {
        icon.className = isDark ? 'fas fa-sun text-amber-400' : 'fas fa-moon text-emerald-800';
    }
}

/**
 * Canlı İstasyon Arama & Kategori Filtreleme
 */
function initSearchAndFilter() {
    const searchInput = document.getElementById('stationSearchInput');
    const categoryBtns = document.querySelectorAll('.category-filter-btn');
    const stationCards = document.querySelectorAll('.station-card');
    
    let currentCategory = 'all';
    let currentSearchQuery = '';

    function filterStations() {
        stationCards.forEach(card => {
            const title = (card.dataset.title || '').toLowerCase();
            const reciter = (card.dataset.reciter || '').toLowerCase();
            const category = card.dataset.category || '';
            const id = parseInt(card.dataset.id);

            const matchesQuery = title.includes(currentSearchQuery) || reciter.includes(currentSearchQuery);
            
            let matchesCategory = false;
            if (currentCategory === 'all') {
                matchesCategory = true;
            } else if (currentCategory === 'favorites') {
                const favs = window.QuranPlayerInstance ? window.QuranPlayerInstance.favorites : [];
                matchesCategory = favs.includes(id);
            } else {
                matchesCategory = (category === currentCategory);
            }

            if (matchesQuery && matchesCategory) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearchQuery = e.target.value.toLowerCase().trim();
            filterStations();
        });
    }

    categoryBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryBtns.forEach(b => {
                b.classList.remove('bg-emerald-600', 'text-white');
                b.classList.add('bg-slate-200', 'dark:bg-emerald-900/40', 'text-slate-800', 'dark:text-emerald-200');
            });

            btn.classList.remove('bg-slate-200', 'dark:bg-emerald-900/40', 'text-slate-800', 'dark:text-emerald-200');
            btn.classList.add('bg-emerald-600', 'text-white');

            currentCategory = btn.dataset.category;
            filterStations();
        });
    });
}

/**
 * Günün Ayeti & Hadisi Mini Ticker Barı
 */
function initDailyAyahTicker() {
    const ayahs = [
        "«Kur'an okunduğu zaman onu dinleyin ve susun ki size merhamet edilsin.» (A'râf Sûresi, 204)",
        "«Şüphesiz Kur'an, en doğru yola iletir.» (İsrâ Sûresi, 9)",
        "«Kalpler ancak Allah'ı anmakla huzur bulur.» (Ra'd Sûresi, 28)",
        "«Biz Kur'an'ı müminlere şifa ve rahmet olarak indirmekteyiz.» (İsrâ Sûresi, 82)",
        "«Sizin en hayırlınız, Kur'an'ı öğrenen ve öğreteninizdir.» (Hadis-i Şerif)"
    ];

    const tickerEl = document.getElementById('dailyAyahTicker');
    if (!tickerEl) return;

    let index = 0;
    setInterval(() => {
        index = (index + 1) % ayahs.length;
        
        const isDesktop = window.innerWidth >= 768;
        if (isDesktop) {
            tickerEl.style.opacity = '0';
            tickerEl.style.transform = 'translateY(-3px) scale(0.98)';
            setTimeout(() => {
                tickerEl.textContent = ayahs[index];
                tickerEl.style.opacity = '1';
                tickerEl.style.transform = 'translateY(0) scale(1)';
            }, 500);
        } else {
            tickerEl.style.opacity = '0';
            setTimeout(() => {
                tickerEl.textContent = ayahs[index];
                tickerEl.style.animation = 'none';
                void tickerEl.offsetWidth; // trigger reflow
                tickerEl.style.animation = '';
                tickerEl.style.opacity = '1';
            }, 400);
        }
    }, 12000);
}

/**
 * 🕌 Namaz Vakitleri & Kalan Süre Sayacı Motoru
 */
function initPrayerTimesEngine() {
    const cityBtn = document.getElementById('changeCityBtn');
    const cityModal = document.getElementById('cityModal');
    const cityClose = document.getElementById('cityModalCloseBtn');
    const citySearch = document.getElementById('citySearchInput');
    const cityGrid = document.getElementById('cityListGrid');
    const cityNameEl = document.getElementById('prayerCityName');

    const cities = [
        "İstanbul", "Ankara", "İzmir", "Bursa", "Antalya", "Adana", "Konya", "Gaziantep", 
        "Şanlıurfa", "Kocaeli", "Mersin", "Diyarbakır", "Hatay", "Manisa", "Kayseri", 
        "Samsun", "Balıkesir", "Kahramanmaraş", "Van", "Aydın", "Denizli", "Sakarya", 
        "Erzurum", "Muğla", "Trabzon", "Elazığ", "Sivas", "Malatya", "Berlin", "Cologne", 
        "Vienna", "Amsterdam", "London", "Paris", "Riyadh"
    ];

    let currentCity = localStorage.getItem('quran_prayer_city') || 'İstanbul';
    if (cityNameEl) cityNameEl.textContent = currentCity;

    function renderCityList(filter = '') {
        if (!cityGrid) return;
        cityGrid.innerHTML = '';
        const filtered = cities.filter(c => c.toLowerCase().includes(filter.toLowerCase()));
        filtered.forEach(c => {
            const btn = document.createElement('button');
            btn.className = `p-2 rounded-xl text-left border transition-all ${c === currentCity ? 'bg-amber-500 text-emerald-950 border-amber-400 font-extrabold' : 'bg-slate-100 dark:bg-emerald-900/40 text-slate-800 dark:text-emerald-200 border-slate-200 dark:border-emerald-800 hover:border-amber-400'}`;
            btn.textContent = c;
            btn.addEventListener('click', () => {
                currentCity = c;
                localStorage.setItem('quran_prayer_city', currentCity);
                if (cityNameEl) cityNameEl.textContent = currentCity;
                fetchPrayerTimes(currentCity);
                if (cityModal) cityModal.classList.add('hidden');
            });
            cityGrid.appendChild(btn);
        });
    }

    if (cityBtn && cityModal) {
        cityBtn.addEventListener('click', () => {
            renderCityList();
            cityModal.classList.remove('hidden');
        });

        if (cityClose) cityClose.addEventListener('click', () => cityModal.classList.add('hidden'));
        if (citySearch) {
            citySearch.addEventListener('input', (e) => renderCityList(e.target.value.trim()));
        }
    }

    let countdownInterval = null;

    function fetchPrayerTimes(cityName) {
        const country = (cityName === 'Berlin' || cityName === 'Cologne') ? 'Germany' : 
                        (cityName === 'Vienna') ? 'Austria' : 
                        (cityName === 'London') ? 'United Kingdom' : 
                        (cityName === 'Paris') ? 'France' : 'Turkey';

        fetch(`https://api.aladhan.com/v1/timingsByCity?city=${encodeURIComponent(cityName)}&country=${encodeURIComponent(country)}&method=13`)
            .then(res => res.json())
            .then(data => {
                if (data && data.data && data.data.timings) {
                    updatePrayerUI(data.data.timings);
                }
            })
            .catch(() => {
                // Fallback static times
                updatePrayerUI({
                    "Fajr": "05:15",
                    "Sunrise": "06:40",
                    "Dhuhr": "13:08",
                    "Asr": "16:25",
                    "Maghrib": "19:15",
                    "Isha": "20:35"
                });
            });
    }

    function updatePrayerUI(timings) {
        document.getElementById('vakitImsak').textContent = `İmsak ${timings.Fajr}`;
        document.getElementById('vakitOgle').textContent = `Öğle ${timings.Dhuhr}`;
        document.getElementById('vakitIkindi').textContent = `İkindi ${timings.Asr}`;
        document.getElementById('vakitAksam').textContent = `Akşam ${timings.Maghrib}`;
        document.getElementById('vakitYatsı').textContent = `Yatsı ${timings.Isha}`;
        document.getElementById('todayTimesBadges').classList.remove('hidden');

        startCountdown(timings);
    }

    function startCountdown(timings) {
        if (countdownInterval) clearInterval(countdownInterval);

        function tick() {
            const now = new Date();
            const prayerTimes = [
                { name: 'İmsak', time: timings.Fajr },
                { name: 'Güneş', time: timings.Sunrise },
                { name: 'Öğle', time: timings.Dhuhr },
                { name: 'İkindi', time: timings.Asr },
                { name: 'Akşam', time: timings.Maghrib },
                { name: 'Yatsı', time: timings.Isha }
            ];

            let nextPrayer = null;
            let nextTargetTime = null;

            for (let p of prayerTimes) {
                const [h, m] = p.time.split(':').map(Number);
                const pDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), h, m, 0);
                if (pDate > now) {
                    nextPrayer = p;
                    nextTargetTime = pDate;
                    break;
                }
            }

            if (!nextPrayer) {
                // Tomorrow's Fajr
                const [h, m] = timings.Fajr.split(':').map(Number);
                nextPrayer = { name: 'İmsak', time: timings.Fajr };
                nextTargetTime = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, h, m, 0);
            }

            const diff = Math.floor((nextTargetTime - now) / 1000);
            const hrs = Math.floor(diff / 3600);
            const mins = Math.floor((diff % 3600) / 60);
            const secs = diff % 60;

            const formatted = `${hrs < 10 ? '0' : ''}${hrs}:${mins < 10 ? '0' : ''}${mins}:${secs < 10 ? '0' : ''}${secs}`;
            
            const countdownEl = document.getElementById('prayerCountdown');
            const textEl = document.getElementById('nextPrayerText');

            if (countdownEl) countdownEl.textContent = formatted;
            if (textEl) textEl.textContent = `${nextPrayer.name} Vaktine Kalan Süre (${nextPrayer.time})`;
        }

        tick();
        countdownInterval = setInterval(tick, 1000);
    }

    fetchPrayerTimes(currentCity);
}

/**
 * 📖 114 Sure & Cüz Rehberi Modalı
 */
function initSurahDirectory() {
    const surahBtn = document.getElementById('surahModalOpenBtn');
    const surahModal = document.getElementById('surahModal');
    const surahClose = document.getElementById('surahModalCloseBtn');
    const surahGrid = document.getElementById('surahGridList');
    const surahSearch = document.getElementById('surahSearchInput');

    let surahsData = [];

    function fetchSurahs() {
        fetch('data/surahs.json')
            .then(res => res.json())
            .then(data => {
                surahsData = data;
                renderSurahs();
            })
            .catch(err => console.error('Surahs load error:', err));
    }

    function renderSurahs(query = '') {
        if (!surahGrid) return;
        surahGrid.innerHTML = '';
        
        const q = query.toLowerCase().trim();
        const filtered = surahsData.filter(s => 
            s.name_tr.toLowerCase().includes(q) || 
            s.name_ar.includes(q) || 
            s.meaning.toLowerCase().includes(q) || 
            String(s.id) === q ||
            String(s.juz).includes(q)
        );

        filtered.forEach(s => {
            const item = document.createElement('div');
            item.className = 'p-3 rounded-2xl bg-slate-100 dark:bg-emerald-900/40 border border-slate-200 dark:border-emerald-800/60 flex items-center justify-between gap-3 hover:border-amber-400 transition-all';
            item.innerHTML = `
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-500 font-extrabold text-xs flex items-center justify-center flex-shrink-0 border border-amber-500/30">
                        ${s.id}
                    </div>
                    <div class="truncate">
                        <h4 class="font-extrabold text-xs text-slate-900 dark:text-white truncate">${s.name_tr}</h4>
                        <p class="text-[10px] text-slate-500 dark:text-emerald-300/80 truncate">${s.meaning}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="font-serif text-sm font-bold text-amber-400 block">${s.name_ar}</span>
                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-950/60 text-emerald-300 font-semibold border border-emerald-800">${s.verses} Ayet • ${s.type}</span>
                </div>
            `;
            surahGrid.appendChild(item);
        });
    }

    if (surahBtn && surahModal) {
        surahBtn.addEventListener('click', () => {
            if (surahsData.length === 0) fetchSurahs();
            surahModal.classList.remove('hidden');
        });

        if (surahClose) surahClose.addEventListener('click', () => surahModal.classList.add('hidden'));
        if (surahSearch) {
            surahSearch.addEventListener('input', (e) => renderSurahs(e.target.value));
        }
    }
}

/**
 * 💬 1-Tıkla Sosyal Medya Paylaşım Modalı & Web Share API
 */
function initShareModal() {
    const shareBtn = document.getElementById('shareModalOpenBtn');
    const shareModal = document.getElementById('shareModal');
    const shareClose = document.getElementById('shareModalCloseBtn');
    const shareStationText = document.getElementById('shareStationText');
    const shareUrlInput = document.getElementById('shareUrlInput');
    const copyBtn = document.getElementById('copyShareUrlBtn');

    const waBtn = document.getElementById('shareWhatsappBtn');
    const tgBtn = document.getElementById('shareTelegramBtn');
    const twBtn = document.getElementById('shareTwitterBtn');

    function getShareData() {
        const player = window.QuranPlayerInstance;
        const station = player ? player.stations[player.currentIndex] : null;
        const title = station ? station.title : "Canlı Kur'an Radyosu";
        const reciter = station ? station.reciter : "Canlı Tilavet";
        const url = window.location.href;
        const text = `«${title} - ${reciter}» 7/24 canlı Kur'an-ı Kerim yayını dinliyorum. Siz de dinleyin:`;
        return { title, reciter, url, text };
    }

    if (shareBtn) {
        shareBtn.addEventListener('click', () => {
            const data = getShareData();
            if (navigator.share && /Android|iPhone|iPad/i.test(navigator.userAgent)) {
                navigator.share({
                    title: data.title,
                    text: data.text,
                    url: data.url
                }).catch(() => {});
            } else if (shareModal) {
                if (shareStationText) shareStationText.textContent = `«${data.title}» canlı yayınını sevdiklerinizle paylaşabilirsiniz.`;
                if (shareUrlInput) shareUrlInput.value = data.url;
                shareModal.classList.remove('hidden');
            }
        });

        if (shareClose && shareModal) {
            shareClose.addEventListener('click', () => shareModal.classList.add('hidden'));
        }

        if (waBtn) {
            waBtn.addEventListener('click', () => {
                const data = getShareData();
                window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(data.text + ' ' + data.url)}`, '_blank');
            });
        }

        if (tgBtn) {
            tgBtn.addEventListener('click', () => {
                const data = getShareData();
                window.open(`https://t.me/share/url?url=${encodeURIComponent(data.url)}&text=${encodeURIComponent(data.text)}`, '_blank');
            });
        }

        if (twBtn) {
            twBtn.addEventListener('click', () => {
                const data = getShareData();
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(data.text)}&url=${encodeURIComponent(data.url)}`, '_blank');
            });
        }

        if (copyBtn && shareUrlInput) {
            copyBtn.addEventListener('click', () => {
                shareUrlInput.select();
                navigator.clipboard.writeText(shareUrlInput.value).then(() => {
                    const orig = copyBtn.textContent;
                    copyBtn.textContent = 'Kopyalandı!';
                    setTimeout(() => copyBtn.textContent = orig, 2000);
                });
            });
        }
    }
}

/**
 * 📲 Masaüstü & Mobil PWA Akıllı Yükleme Promptu
 */
let deferredPwaPrompt = null;
function initPwaInstallPrompt() {
    const pwaBtn = document.getElementById('pwaInstallBtn');
    const pwaModal = document.getElementById('pwaModal');
    const pwaClose = document.getElementById('pwaModalCloseBtn');
    const triggerBtn = document.getElementById('triggerPwaPromptBtn');
    const mobileBanner = document.getElementById('mobilePwaBanner');
    const mobileInstallBtn = document.getElementById('mobilePwaInstallBtn');
    const mobileCloseBtn = document.getElementById('mobilePwaCloseBtn');

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    const isMobile = window.innerWidth < 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isDismissed = sessionStorage.getItem('pwa_banner_dismissed') === 'true';

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPwaPrompt = e;
        console.log('beforeinstallprompt captured!');
    });

    // Auto-show mobile bottom install banner on mobile devices if not standalone and not dismissed
    if (mobileBanner && !isStandalone && isMobile && !isDismissed) {
        setTimeout(() => {
            mobileBanner.classList.remove('hidden');
        }, 800);
    }

    window.addEventListener('appinstalled', () => {
        console.log('Kur\'an Radyo PWA başarıyla kuruldu!');
        deferredPwaPrompt = null;
        if (pwaModal) pwaModal.classList.add('hidden');
        if (mobileBanner) mobileBanner.classList.add('hidden');
    });

    function executeInstall() {
        if (mobileBanner) mobileBanner.classList.add('hidden');

        if (deferredPwaPrompt) {
            deferredPwaPrompt.prompt();
            deferredPwaPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('PWA installation accepted');
                }
                deferredPwaPrompt = null;
                if (pwaModal) pwaModal.classList.add('hidden');
            });
        } else {
            if (pwaModal) pwaModal.classList.remove('hidden');
        }
    }

    if (pwaBtn) {
        pwaBtn.addEventListener('click', () => executeInstall());
    }

    if (mobileInstallBtn) {
        mobileInstallBtn.addEventListener('click', () => executeInstall());
    }

    if (mobileCloseBtn && mobileBanner) {
        mobileCloseBtn.addEventListener('click', () => {
            mobileBanner.classList.add('hidden');
            sessionStorage.setItem('pwa_banner_dismissed', 'true');
        });
    }

    if (triggerBtn) {
        triggerBtn.addEventListener('click', () => executeInstall());
    }

    if (pwaClose && pwaModal) {
        pwaClose.addEventListener('click', () => pwaModal.classList.add('hidden'));
    }
}

/**
 * Sitene Ekle (Embed Code Generator Modal)
 */
function initEmbedModal() {
    const embedBtn = document.getElementById('embedModalOpenBtn');
    const embedModal = document.getElementById('embedModal');
    const embedModalClose = document.getElementById('embedModalCloseBtn');
    const copyEmbedBtn = document.getElementById('copyEmbedCodeBtn');
    const embedCodeInput = document.getElementById('embedCodeInput');

    const radioSelect = document.getElementById('embedRadioSelect');
    const themeSelect = document.getElementById('embedThemeSelect');
    const styleSelect = document.getElementById('embedStyleSelect');
    const previewIframe = document.getElementById('embedPreviewIframe');

    function updateWidgetCodeAndPreview() {
        if (!radioSelect || !themeSelect || !styleSelect) return;
        const slug = radioSelect.value || 'genel-kuran-radyosu';
        const theme = themeSelect.value || 'dark';
        const style = styleSelect.value || 'card';

        let baseUrl = (window.SITE_BASE_URL || window.location.origin).replace(/\/+$/, '');
        if (!window.SITE_BASE_URL || window.SITE_BASE_URL === '/') {
            let path = window.location.pathname
                .replace(/\/radyo\/[^\/]+/, '')
                .replace(/\/embed\/[^\/]+/, '')
                .replace(/\/index\.php$/, '')
                .replace(/\/$/, '');
            baseUrl = (window.location.origin + path).replace(/\/+$/, '');
        }

        const embedSrc = `${baseUrl}/embed.php?slug=${slug}&theme=${theme}&style=${style}`;
        const height = style === 'bar' ? '84' : '170';

        const iframeCode = `<iframe src="${embedSrc}" width="100%" height="${height}" frameborder="0" scrolling="no" style="border-radius:20px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.25);"></iframe>`;

        if (embedCodeInput) embedCodeInput.value = iframeCode;
        if (previewIframe) {
            previewIframe.style.height = `${height}px`;
            if (previewIframe.getAttribute('data-last-src') !== embedSrc) {
                previewIframe.src = embedSrc;
                previewIframe.setAttribute('data-last-src', embedSrc);
            }
        }
    }

    if (embedBtn && embedModal) {
        embedBtn.addEventListener('click', () => {
            const player = window.QuranPlayerInstance;
            if (player && player.stations[player.currentIndex]) {
                const currentStation = player.stations[player.currentIndex];
                if (radioSelect && (currentStation.slug || currentStation.id)) {
                    radioSelect.value = currentStation.slug || currentStation.id;
                }
            }
            updateWidgetCodeAndPreview();
            embedModal.classList.remove('hidden');
        });

        if (radioSelect) radioSelect.addEventListener('change', updateWidgetCodeAndPreview);
        if (themeSelect) themeSelect.addEventListener('change', updateWidgetCodeAndPreview);
        if (styleSelect) styleSelect.addEventListener('change', updateWidgetCodeAndPreview);

        if (embedModalClose) {
            embedModalClose.addEventListener('click', () => {
                embedModal.classList.add('hidden');
            });
        }

        if (copyEmbedBtn && embedCodeInput) {
            copyEmbedBtn.addEventListener('click', () => {
                embedCodeInput.select();
                navigator.clipboard.writeText(embedCodeInput.value).then(() => {
                    const originalText = copyEmbedBtn.innerHTML;
                    copyEmbedBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Kopyalandı!';
                    copyEmbedBtn.classList.add('bg-emerald-600', 'text-white');
                    setTimeout(() => {
                        copyEmbedBtn.innerHTML = originalText;
                        copyEmbedBtn.classList.remove('bg-emerald-600', 'text-white');
                    }, 2000);
                });
            });
        }
    }
}

/**
 * Uyku Zamanlayıcısı Modal UI
 */
function initSleepTimerModal() {
    const timerBtn = document.getElementById('sleepTimerBtn');
    const timerModal = document.getElementById('sleepTimerModal');
    const timerClose = document.getElementById('sleepTimerCloseBtn');
    const timerOptions = document.querySelectorAll('.sleep-option-btn');

    if (timerBtn && timerModal) {
        timerBtn.addEventListener('click', () => {
            timerModal.classList.remove('hidden');
        });

        if (timerClose) {
            timerClose.addEventListener('click', () => {
                timerModal.classList.add('hidden');
            });
        }

        timerOptions.forEach(opt => {
            opt.addEventListener('click', () => {
                const mins = parseInt(opt.dataset.minutes);
                if (window.QuranPlayerInstance) {
                    window.QuranPlayerInstance.setSleepTimer(mins);
                }
                timerModal.classList.add('hidden');
            });
        });
    }
}

/**
 * Register PWA Service Worker
 */
function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js')
            .then(reg => console.log('PWA ServiceWorker Active:', reg.scope))
            .catch(err => console.log('ServiceWorker registration skipped:', err));
    }
}
