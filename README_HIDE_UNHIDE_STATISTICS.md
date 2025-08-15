# Fitur Hide/Unhide Statistics Dashboard

## Deskripsi
Fitur ini menambahkan kemampuan untuk menyembunyikan dan menampilkan kembali kartu statistik dashboard (Total Peserta, Status Done, Status Already, Status On Target) dengan versi desktop dan mobile yang terpisah. Data statistik secara default disembunyikan.

## Fitur Utama

### 1. Toggle Button
- **Desktop**: Tombol toggle yang hanya muncul di layar desktop (≥768px)
- **Mobile**: Tombol toggle yang hanya muncul di layar mobile (<768px)
- **State Persistence**: Status show/hide disimpan di localStorage

### 2. Responsive Design
- **Desktop Version**: Layout 4 kolom horizontal
- **Mobile Version**: Layout 2x2 grid dengan kartu yang dioptimalkan untuk mobile

### 3. Animasi
- **Slide Down**: Animasi saat menampilkan statistik
- **Slide Up**: Animasi saat menyembunyikan statistik
- **Smooth Transitions**: Transisi halus untuk semua elemen

## Struktur HTML

### Toggle Button Section
```html
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar text-primary"></i> 
                Statistik Dashboard
            </h5>
            <div class="toggle-buttons">
                <!-- Desktop Toggle -->
                <button class="btn btn-outline-primary btn-sm d-none d-md-inline-block toggle-stats-btn" 
                        data-target="desktop-stats" 
                        data-action="toggle">
                    <i class="fas fa-eye-slash"></i> 
                    <span class="toggle-text">Tampilkan</span> Statistik
                </button>
                <!-- Mobile Toggle -->
                <button class="btn btn-outline-primary btn-sm d-md-none toggle-stats-btn" 
                        data-target="mobile-stats" 
                        data-action="toggle">
                    <i class="fas fa-eye-slash"></i> 
                    <span class="toggle-text">Tampilkan</span> Statistik
                </button>
            </div>
        </div>
    </div>
</div>
```

### Desktop Statistics
```html
<div class="row mb-4 stats-container" id="desktop-stats" style="display: none;">
    <!-- 4 kolom kartu statistik -->
</div>
```

### Mobile Statistics
```html
<div class="row mb-4 stats-container d-md-none" id="mobile-stats" style="display: none;">
    <!-- 2x2 grid kartu statistik -->
</div>
```

## CSS Features

### Toggle Button Styling
```css
.toggle-stats-btn {
    border-radius: var(--border-radius);
    padding: 0.5rem 1rem;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid var(--primary-color);
    background: transparent;
    color: var(--primary-color);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
```

### Mobile Stats Cards
```css
.mobile-stats-card {
    padding: 1rem;
    text-align: center;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-left: 4px solid var(--gold);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.95));
    position: relative;
    overflow: hidden;
}
```

### Animation Classes
```css
.stats-container.show {
    animation: slideDown 0.3s ease-out;
}

.stats-container.hide {
    animation: slideUp 0.3s ease-out;
}
```

## JavaScript Functionality

### Toggle Handler
```javascript
document.addEventListener('click', function(e) {
    if (e.target.closest('.toggle-stats-btn')) {
        var button = e.target.closest('.toggle-stats-btn');
        var target = button.getAttribute('data-target');
        var action = button.getAttribute('data-action');
        var targetElement = document.getElementById(target);
        
        // Toggle logic
        if (action === 'toggle') {
            if (targetElement.style.display === 'none' || targetElement.style.display === '') {
                // Show statistics
                showStats(targetElement, button);
            } else {
                // Hide statistics
                hideStats(targetElement, button);
            }
        }
    }
});
```

### State Persistence
```javascript
// Save state to localStorage
localStorage.setItem('stats_' + target + '_visible', 'true');

// Load saved state from localStorage
function loadStatsState() {
    var desktopVisible = localStorage.getItem('stats_desktop-stats_visible');
    var mobileVisible = localStorage.getItem('stats_mobile-stats_visible');
    
    if (desktopVisible === 'true') {
        // Restore desktop state
    }
    
    if (mobileVisible === 'true') {
        // Restore mobile state
    }
}
```

## Responsive Behavior

### Desktop (≥768px)
- Toggle button muncul di sebelah kanan header
- Layout 4 kolom horizontal
- Kartu statistik dengan layout row/column

### Mobile (<768px)
- Toggle button full width di bawah header
- Layout 2x2 grid
- Kartu statistik yang dioptimalkan untuk mobile
- Font size dan padding yang disesuaikan

## State Management

### Default State
- **Desktop Stats**: Hidden (`display: none`)
- **Mobile Stats**: Hidden (`display: none`)
- **Button Text**: "Tampilkan Statistik"
- **Button Icon**: `fa-eye-slash`

### After Toggle
- **Desktop Stats**: Visible dengan animasi slideDown
- **Mobile Stats**: Visible dengan animasi slideDown
- **Button Text**: "Sembunyikan Statistik"
- **Button Icon**: `fa-eye`

### Persistence
- State disimpan di `localStorage`
- Key: `stats_desktop-stats_visible` dan `stats_mobile-stats_visible`
- Value: `'true'` atau `'false'`

## Usage

### Cara Penggunaan
1. **Desktop**: Klik tombol "Tampilkan Statistik" di sebelah kanan header
2. **Mobile**: Klik tombol "Tampilkan Statistik" di bawah header
3. **Toggle**: Klik tombol yang sama untuk menyembunyikan statistik
4. **Persistence**: Status akan diingat saat refresh halaman

### Browser Support
- **localStorage**: Semua browser modern
- **CSS Grid**: Semua browser modern
- **Flexbox**: Semua browser modern
- **CSS Animations**: Semua browser modern

## Customization

### Mengubah Default State
Untuk mengubah default state menjadi visible, edit CSS:
```css
#desktop-stats, #mobile-stats {
    display: block !important;
}
```

### Mengubah Animasi
Untuk mengubah durasi animasi:
```css
.stats-container.show {
    animation: slideDown 0.5s ease-out; /* Ubah dari 0.3s */
}
```

### Mengubah Warna
Untuk mengubah warna toggle button:
```css
.toggle-stats-btn {
    border-color: #your-color;
    color: #your-color;
}

.toggle-stats-btn:hover {
    background: #your-color;
}
```

## Troubleshooting

### Statistik tidak muncul
- Periksa apakah ada error JavaScript di console
- Pastikan localStorage tidak di-disable
- Periksa apakah ID element sesuai

### Animasi tidak berfungsi
- Pastikan browser mendukung CSS animations
- Periksa apakah ada konflik CSS

### State tidak tersimpan
- Periksa apakah localStorage tersedia
- Pastikan tidak ada error JavaScript
- Coba clear localStorage dan test ulang
