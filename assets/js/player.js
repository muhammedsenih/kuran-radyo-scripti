/**
 * Canlı Kur'an-ı Kerim Radyosu - Gelişmiş Ses Motoru & MediaSession Kontrolcüsü
 */

class QuranPlayer {
    constructor() {
        this.audio = new Audio();
        this.audio.preload = 'none';
           this.stations = [];
        this.currentIndex = 0;
        this.isPlaying = false;
        this.isMuted = false;
        this.volume = 0.8;
        this.triedBackup = false;
        this.retryCount = 0;
        this.maxRetries = 4;
        this.retryTimer = null;
        
        this.favorites = JSON.parse(localStorage.getItem('quran_favorites') || '[]');
        this.sleepTimerId = null;
        this.sleepTimeRemaining = 0;
        
        // Visualizer Canvas
        this.canvas = null;
        this.ctx = null;
        this.animFrameId = null;
        this.waveOffset = 0;
        
        this.initDOM();
        this.bindEvents();
    }

    initDOM() {
        this.playBtn = document.getElementById('playPauseBtn');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.volumeSlider = document.getElementById('volumeSlider');
        this.muteBtn = document.getElementById('muteBtn');
        
        this.titleEl = document.getElementById('currentTitle');
        this.reciterEl = document.getElementById('currentReciter');
        this.categoryEl = document.getElementById('currentCategory');
        this.logoEl = document.getElementById('currentLogo');
        this.statusBadge = document.getElementById('statusBadge');
        
        this.favoriteToggleBtn = document.getElementById('favoriteToggleBtn');
        this.favIcon = document.getElementById('favIcon');
        
        this.sleepTimerBtn = document.getElementById('sleepTimerBtn');
        this.sleepTimerBadge = document.getElementById('sleepTimerBadge');
        
        this.canvas = document.getElementById('visualizerCanvas');
        if (this.canvas) {
            this.ctx = this.canvas.getContext('2d');
            this.resizeCanvas();
            window.addEventListener('resize', () => this.resizeCanvas());
            this.startVisualizer();
        }

        const savedVol = localStorage.getItem('quran_volume');
        if (savedVol !== null) {
            this.volume = parseFloat(savedVol);
            this.audio.volume = this.volume;
            if (this.volumeSlider) this.volumeSlider.value = this.volume;
        } else {
            this.audio.volume = this.volume;
        }
    }

    setDefaultCover(url) {
        this.defaultCover = url || '';
    }

    setStations(stationsList) {
        this.stations = stationsList;
        let activeIdx = -1;

        if (window.INITIAL_RADIO_SLUG) {
            activeIdx = this.stations.findIndex(s => s.slug === window.INITIAL_RADIO_SLUG || String(s.id) === String(window.INITIAL_RADIO_SLUG));
        }

        if (activeIdx === -1) {
            const defIndex = this.stations.findIndex(s => s.is_default);
            activeIdx = (defIndex !== -1) ? defIndex : 0;
        }

        this.currentIndex = activeIdx;
        this.loadStation(this.currentIndex, false, true);
    }

    loadStation(index, autoPlay = false, isInitial = false) {
        if (!this.stations || this.stations.length === 0) return;
        
        if (index < 0) index = this.stations.length - 1;
        if (index >= this.stations.length) index = 0;
        
        if (this.retryTimer) clearTimeout(this.retryTimer);
        this.currentIndex = index;
        this.triedBackup = false;
        this.retryCount = 0;
        const station = this.stations[this.currentIndex];
        
        this.audio.src = station.stream_url;
        this.audio.load();
        
        if (this.titleEl) this.titleEl.textContent = station.title;
        if (this.reciterEl) this.reciterEl.textContent = station.reciter;
        if (this.categoryEl) this.categoryEl.textContent = station.category;
        
        const coverSrc = (station.logo && station.logo.trim() !== '') ? station.logo : (this.defaultCover || window.DEFAULT_COVER_URL || '');
        const iconEl = document.getElementById('currentLogoIcon');
        if (this.logoEl) {
            if (coverSrc) {
                this.logoEl.src = coverSrc;
                this.logoEl.classList.remove('hidden');
                if (iconEl) iconEl.classList.add('hidden');
            } else {
                this.logoEl.removeAttribute('src');
                this.logoEl.classList.add('hidden');
                if (iconEl) iconEl.classList.remove('hidden');
            }
        }
        
        this.updateFavoriteUI();
        this.highlightActiveCard(station.id);
        this.updateMediaSession(station);
        this.updateUrlAndSeoTitle(station, isInitial);

        if (autoPlay) {
            this.play();
        } else {
            this.isPlaying = false;
            this.updatePlayBtnUI();
            this.updateStatus('', '');
        }
    }

    updateUrlAndSeoTitle(station, isInitial = false) {
        if (!station) return;
        const slug = station.slug || 'radyo';
        const pageTitle = `${station.title} Canlı Dinle - Kur'an Radyo & Tilavet Portalı`;
        document.title = pageTitle;

        if (window.history && (window.history.pushState || window.history.replaceState)) {
            const baseUrl = window.SITE_BASE_URL || (window.location.origin + '/');
            const targetUrl = `${baseUrl}radyo/${slug}`;
            try {
                if (isInitial) {
                    window.history.replaceState({ stationId: station.id, slug: slug }, pageTitle, targetUrl);
                } else {
                    window.history.pushState({ stationId: station.id, slug: slug }, pageTitle, targetUrl);
                }
            } catch(e) {
                if (isInitial) {
                    window.history.replaceState({ stationId: station.id, slug: slug }, pageTitle, '?r=' + slug);
                } else {
                    window.history.pushState({ stationId: station.id, slug: slug }, pageTitle, '?r=' + slug);
                }
            }
        }
    }

    play() {
        const station = this.stations[this.currentIndex];
        if (!station) return;

        if (this.retryTimer) clearTimeout(this.retryTimer);
        
        const currentSrc = (this.triedBackup && station.backup_url) ? station.backup_url : station.stream_url;
        if (!this.audio.src || this.audio.src !== currentSrc) {
            this.audio.src = currentSrc;
            this.audio.load();
        }
        
        this.updateStatus('YÜKLENİYOR...', 'bg-amber-500/20 text-amber-300 animate-pulse');
        
        const playPromise = this.audio.play();
        if (playPromise !== undefined) {
            playPromise.then(() => {
                this.isPlaying = true;
                this.retryCount = 0;
                this.updatePlayBtnUI();
                this.updateStatus('CANLI YAYIN', 'bg-red-500/20 text-red-400 font-bold');
                document.body.classList.add('is-playing');
                this.startVisualizer();
            }).catch(error => {
                console.warn(`Playback attempt ${this.retryCount + 1} error:`, error);
                this.handleStreamError();
            });
        }
    }

    handleStreamError() {
        if (this.retryTimer) clearTimeout(this.retryTimer);
        const station = this.stations[this.currentIndex];
        if (!station) return;

        // Try backup URL first if available and not tried yet
        if (station.backup_url && !this.triedBackup) {
            this.triedBackup = true;
            this.audio.src = station.backup_url;
            this.audio.load();
            this.play();
            return;
        }

        // Auto retry up to maxRetries times (5 total attempts)
        if (this.retryCount < this.maxRetries) {
            this.retryCount++;
            this.updateStatus('YÜKLENİYOR...', 'bg-amber-500/20 text-amber-300 animate-pulse');

            this.retryTimer = setTimeout(() => {
                if (this.retryCount >= 2 && station.stream_url) {
                    this.triedBackup = !this.triedBackup;
                }
                const src = (this.triedBackup && station.backup_url) ? station.backup_url : station.stream_url;
                this.audio.src = src;
                this.audio.load();
                this.play();
            }, 1500);
        } else {
            // Max retries reached
            this.isPlaying = false;
            this.retryCount = 0;
            this.updatePlayBtnUI();
            this.updateStatus('YAYIN YÜKLENEMEDİ', 'bg-rose-500/20 text-rose-300 font-bold');
            document.body.classList.remove('is-playing');
        }
    }

    pause() {
        if (this.retryTimer) clearTimeout(this.retryTimer);
        this.audio.pause();
        this.isPlaying = false;
        this.updatePlayBtnUI();
        this.updateStatus('DURDURULDU', 'bg-slate-500/30 text-slate-200 font-bold border border-slate-500/40');
        document.body.classList.remove('is-playing');
        this.stopVisualizer();
    }

    togglePlay() {
        if (this.isPlaying) {
            this.pause();
        } else {
            // On manual play click, force fresh stream reload and reset retry counters
            const station = this.stations[this.currentIndex];
            if (station) {
                this.retryCount = 0;
                this.triedBackup = false;
                const src = station.stream_url;
                this.audio.src = src;
                this.audio.load();
            }
            this.play();
        }
    }

    next() {
        let nextIndex = this.currentIndex + 1;
        if (nextIndex >= this.stations.length) nextIndex = 0;
        this.loadStation(nextIndex, true);
    }

    prev() {
        let prevIndex = this.currentIndex - 1;
        if (prevIndex < 0) prevIndex = this.stations.length - 1;
        this.loadStation(prevIndex, true);
    }

    setVolume(val) {
        this.volume = parseFloat(val);
        this.audio.volume = this.volume;
        this.isMuted = (this.volume === 0);
        this.updateMuteUI();
        localStorage.setItem('quran_volume', this.volume);
    }

    toggleMute() {
        if (this.isMuted) {
            this.audio.volume = this.volume > 0 ? this.volume : 0.8;
            this.isMuted = false;
        } else {
            this.audio.volume = 0;
            this.isMuted = true;
        }
        this.updateMuteUI();
    }

    updateMuteUI() {
        if (this.muteBtn) {
            const icon = this.muteBtn.querySelector('i');
            if (icon) {
                if (this.isMuted || this.audio.volume === 0) {
                    icon.className = 'fas fa-volume-mute text-rose-400';
                } else if (this.audio.volume < 0.5) {
                    icon.className = 'fas fa-volume-down text-emerald-400';
                } else {
                    icon.className = 'fas fa-volume-up text-emerald-400';
                }
            }
        }
    }

    updatePlayBtnUI() {
        if (this.playBtn) {
            const icon = this.playBtn.querySelector('i');
            if (icon) {
                icon.className = this.isPlaying ? 'fas fa-pause' : 'fas fa-play ml-1';
            }
        }
    }

    updateStatus(text, bgClass) {
        if (this.statusBadge) {
            if (!text) {
                this.statusBadge.classList.add('hidden');
                this.statusBadge.textContent = '';
            } else {
                this.statusBadge.classList.remove('hidden');
                this.statusBadge.textContent = text;
                this.statusBadge.className = `px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider ${bgClass}`;
            }
        }
    }

    highlightActiveCard(stationId) {
        document.querySelectorAll('.station-card').forEach(card => {
            const id = parseInt(card.dataset.id);
            if (id === stationId) {
                card.classList.add('active-playing');
                const btn = card.querySelector('.play-station-btn i');
                if (btn) btn.className = this.isPlaying ? 'fas fa-pause' : 'fas fa-play';
            } else {
                card.classList.remove('active-playing');
                const btn = card.querySelector('.play-station-btn i');
                if (btn) btn.className = 'fas fa-play';
            }
        });
    }

    updateMediaSession(station) {
        if ('mediaSession' in navigator) {
            navigator.mediaSession.metadata = new MediaMetadata({
                title: station.title,
                artist: station.reciter,
                album: "Canlı Kur'an-ı Kerim Radyosu",
                artwork: (station.logo || window.DEFAULT_COVER_URL) ? [
                    { src: station.logo || window.DEFAULT_COVER_URL, sizes: '512x512', type: 'image/png' }
                ] : []
            });

            navigator.mediaSession.setActionHandler('play', () => this.play());
            navigator.mediaSession.setActionHandler('pause', () => this.pause());
            navigator.mediaSession.setActionHandler('previoustrack', () => this.prev());
            navigator.mediaSession.setActionHandler('nexttrack', () => this.next());
        }
    }

    toggleFavorite() {
        const currentStation = this.stations[this.currentIndex];
        if (!currentStation) return;
        
        const index = this.favorites.indexOf(currentStation.id);
        let isFav = false;
        if (index === -1) {
            this.favorites.push(currentStation.id);
            isFav = true;
        } else {
            this.favorites.splice(index, 1);
            isFav = false;
        }
        
        localStorage.setItem('quran_favorites', JSON.stringify(this.favorites));
        this.updateFavoriteUI();

        // Backend İstatistik Kaydı (Favori Sayısı)
        fetch(`api.php?action=favorite_toggle&radio_id=${currentStation.id}&is_favorite=${isFav ? 1 : 0}&title=${encodeURIComponent(currentStation.title)}`).catch(() => {});
    }

    updateFavoriteUI() {
        const currentStation = this.stations[this.currentIndex];
        if (!currentStation || !this.favIcon) return;
        
        const isFav = this.favorites.includes(currentStation.id);
        if (isFav) {
            this.favIcon.className = 'fas fa-heart text-rose-500';
        } else {
            this.favIcon.className = 'far fa-heart text-gray-400 hover:text-rose-400';
        }
    }

    setSleepTimer(minutes) {
        if (this.sleepTimerId) {
            clearInterval(this.sleepTimerId);
            this.sleepTimerId = null;
        }

        if (minutes <= 0) {
            this.sleepTimeRemaining = 0;
            if (this.sleepTimerBadge) this.sleepTimerBadge.classList.add('hidden');
            return;
        }

        this.sleepTimeRemaining = minutes * 60;
        this.updateSleepBadge();

        this.sleepTimerId = setInterval(() => {
            this.sleepTimeRemaining--;
            if (this.sleepTimeRemaining <= 0) {
                clearInterval(this.sleepTimerId);
                this.sleepTimerId = null;
                this.pause();
                if (this.sleepTimerBadge) this.sleepTimerBadge.classList.add('hidden');
            } else {
                this.updateSleepBadge();
            }
        }, 1000);
    }

    updateSleepBadge() {
        if (!this.sleepTimerBadge) return;
        const mins = Math.floor(this.sleepTimeRemaining / 60);
        const secs = this.sleepTimeRemaining % 60;
        const formatted = `${mins}:${secs < 10 ? '0' : ''}${secs}`;
        this.sleepTimerBadge.querySelector('span').textContent = formatted;
        this.sleepTimerBadge.classList.remove('hidden');
    }

    resizeCanvas() {
        if (!this.canvas) return;
        this.canvas.width = this.canvas.parentElement.clientWidth || 600;
        this.canvas.height = 50;
    }

    startVisualizer() {
        if (!this.ctx) return;
        if (this.animFrameId) cancelAnimationFrame(this.animFrameId);
        
        const render = () => {
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            const barWidth = 4;
            const gap = 3;
            const totalBars = Math.floor(this.canvas.width / (barWidth + gap));
            this.waveOffset += 0.08;
            
            for (let i = 0; i < totalBars; i++) {
                let height = 4;
                if (this.isPlaying) {
                    const sinVal1 = Math.sin(i * 0.15 + this.waveOffset);
                    const sinVal2 = Math.cos(i * 0.25 - this.waveOffset * 1.2);
                    height = Math.abs(sinVal1 + sinVal2) * 16 + 5;
                }
                
                const x = i * (barWidth + gap);
                const y = (this.canvas.height - height) / 2;
                
                const grad = this.ctx.createLinearGradient(0, y, 0, y + height);
                grad.addColorStop(0, '#FCE081');
                grad.addColorStop(0.5, '#D4AF37');
                grad.addColorStop(1, '#10B981');
                
                this.ctx.fillStyle = grad;
                this.ctx.beginPath();
                this.ctx.roundRect(x, y, barWidth, height, 2);
                this.ctx.fill();
            }
            
            this.animFrameId = requestAnimationFrame(render);
        };
        
        render();
    }

    stopVisualizer() {
        if (!this.animFrameId && this.ctx) {
            this.startVisualizer();
        }
    }

    sendPlayStat() {
        const station = this.stations[this.currentIndex];
        if (!station) return;
        fetch(`api.php?action=ping_play&radio_id=${station.id}&title=${encodeURIComponent(station.title)}`).catch(() => {});
    }

    bindEvents() {
        if (this.playBtn) this.playBtn.addEventListener('click', () => this.togglePlay());
        if (this.prevBtn) this.prevBtn.addEventListener('click', () => this.prev());
        if (this.nextBtn) this.nextBtn.addEventListener('click', () => this.next());
        if (this.volumeSlider) this.volumeSlider.addEventListener('input', (e) => this.setVolume(e.target.value));
        if (this.muteBtn) this.muteBtn.addEventListener('click', () => this.toggleMute());
        if (this.favoriteToggleBtn) this.favoriteToggleBtn.addEventListener('click', () => this.toggleFavorite());
        
        this.audio.addEventListener('playing', () => {
            this.isPlaying = true;
            this.updatePlayBtnUI();
            this.updateStatus('CANLI YAYIN', 'bg-red-500/20 text-red-400 font-bold');
            this.sendPlayStat();
        });

        this.audio.addEventListener('waiting', () => {
            this.updateStatus('TAMPONLANIYOR...', 'bg-amber-500/20 text-amber-300 animate-pulse');
        });
        
        this.audio.addEventListener('error', () => {
            this.handleStreamError();
        });
    }
}

window.QuranPlayerInstance = new QuranPlayer();
