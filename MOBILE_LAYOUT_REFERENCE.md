# Referensi Tampilan Mobile untuk Jadwal Kunjungan

## 1. Layout Default (Current)
- **Deskripsi**: Layout vertikal dengan card yang berisi informasi tanggal, jumlah peserta, dan detail jam kunjungan
- **Kelebihan**: Informasi lengkap, mudah dibaca
- **Kekurangan**: Memakan banyak ruang vertikal
- **Cocok untuk**: Tablet dan mobile landscape

## 2. Layout Compact (New)
- **Deskripsi**: Versi yang lebih padat dengan spacing yang dikurangi
- **Kelebihan**: Lebih banyak data terlihat dalam satu layar
- **Kekurangan**: Informasi lebih kecil, perlu fokus lebih
- **Cocok untuk**: Mobile portrait, layar kecil

## 3. Layout Card-based (Alternative)
```css
.schedule-card-layout {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 0.5rem;
}
```
- **Deskripsi**: Layout grid dengan card terpisah untuk setiap tanggal
- **Kelebihan**: Mudah scan, visual yang bersih
- **Kekurangan**: Memakan ruang horizontal
- **Cocok untuk**: Desktop, tablet landscape

## 4. Layout List (Alternative)
```css
.schedule-list-layout {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
```
- **Deskripsi**: Layout list horizontal dengan informasi tanggal dan jumlah
- **Kelebihan**: Sangat compact, mudah scroll
- **Kekurangan**: Detail jam tersembunyi (perlu expand)
- **Cocok untuk**: Mobile portrait, quick overview

## 5. Layout Timeline (Alternative)
```css
.schedule-timeline {
    position: relative;
    padding-left: 20px;
}

.schedule-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--primary-color);
}
```
- **Deskripsi**: Layout timeline vertikal dengan garis penghubung
- **Kelebihan**: Visual yang menarik, menunjukkan urutan waktu
- **Kekurangan**: Memakan ruang vertikal
- **Cocok untuk**: Tablet, mobile landscape

## 6. Layout Accordion (Alternative)
```css
.schedule-accordion {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.schedule-accordion-header {
    padding: 0.75rem;
    background: #f8f9fa;
    cursor: pointer;
    border-bottom: 1px solid #ddd;
}
```
- **Deskripsi**: Layout accordion dengan expand/collapse
- **Kelebihan**: Sangat compact, detail tersembunyi
- **Kekurangan**: Perlu interaksi untuk melihat detail
- **Cocok untuk**: Mobile portrait, data yang banyak

## 7. Layout Table Mobile (Alternative)
```css
.schedule-table-mobile {
    display: table;
    width: 100%;
    font-size: 0.8rem;
}

.schedule-table-mobile .table-row {
    display: table-row;
}

.schedule-table-mobile .table-cell {
    display: table-cell;
    padding: 0.25rem;
    border-bottom: 1px solid #eee;
}
```
- **Deskripsi**: Layout table yang dioptimalkan untuk mobile
- **Kelebihan**: Data terstruktur, mudah scan
- **Kekurangan**: Sulit untuk data yang kompleks
- **Cocok untuk**: Data sederhana, quick reference

## 8. Layout Swipeable Cards (Alternative)
```css
.schedule-swipe-container {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    gap: 0.5rem;
    padding: 0.5rem;
}

.schedule-swipe-card {
    flex: 0 0 300px;
    scroll-snap-align: start;
    background: white;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
```
- **Deskripsi**: Layout horizontal swipe dengan card
- **Kelebihan**: Interaktif, modern UI
- **Kekurangan**: Hanya satu card terlihat
- **Cocok untuk**: Mobile portrait, focus pada satu tanggal

## Implementasi yang Sudah Ditambahkan

### 1. CSS Optimizations
- ✅ Reduced padding dan margin untuk mobile
- ✅ Smaller font sizes untuk layar kecil
- ✅ Compact badges dan buttons
- ✅ Optimized spacing untuk touch targets

### 2. Responsive Breakpoints
- ✅ `@media (max-width: 768px)` - Tablet dan mobile
- ✅ `@media (max-width: 480px)` - Mobile kecil
- ✅ Progressive enhancement untuk berbagai ukuran layar

### 3. Layout Toggle
- ✅ Toggle button untuk switch antara default dan compact layout
- ✅ LocalStorage untuk menyimpan preferensi user
- ✅ Smooth transitions antara layout

### 4. Touch Optimizations
- ✅ Minimum 44px touch targets
- ✅ Adequate spacing untuk prevent accidental taps
- ✅ Optimized button sizes untuk mobile

## Rekomendasi Penggunaan

### Untuk Mobile Portrait (< 768px)
1. **Primary**: Layout Compact (sudah diimplementasi)
2. **Alternative**: Layout List atau Accordion
3. **Fallback**: Layout Default dengan optimizations

### Untuk Tablet (768px - 1024px)
1. **Primary**: Layout Default dengan compact elements
2. **Alternative**: Layout Card-based
3. **Fallback**: Layout Timeline

### Untuk Desktop (> 1024px)
1. **Primary**: Layout Default
2. **Alternative**: Layout Card-based atau Table
3. **Fallback**: Layout Grid

## Tips Implementasi Selanjutnya

1. **Add More Layout Options**:
   ```javascript
   // Add more layout toggles
   const layouts = ['default', 'compact', 'card', 'list', 'timeline'];
   ```

2. **Implement Virtual Scrolling**:
   ```javascript
   // For large datasets
   const virtualScroller = new VirtualScroller({
       container: '.schedule-stats',
       itemHeight: 60
   });
   ```

3. **Add Search/Filter**:
   ```javascript
   // Quick search functionality
   function filterSchedules(query) {
       const items = document.querySelectorAll('.schedule-item');
       items.forEach(item => {
           const text = item.textContent.toLowerCase();
           item.style.display = text.includes(query) ? 'block' : 'none';
       });
   }
   ```

4. **Implement Pull-to-Refresh**:
   ```javascript
   // Mobile-friendly refresh
   const pullToRefresh = new PullToRefresh({
       container: '.schedule-stats',
       onRefresh: () => location.reload()
   });
   ```

## Performance Considerations

1. **Lazy Loading**: Load schedule data as needed
2. **Image Optimization**: Use WebP format for icons
3. **CSS Optimization**: Minimize unused CSS
4. **JavaScript Optimization**: Debounce scroll events
5. **Caching**: Cache schedule data in localStorage

## Accessibility Features

1. **Keyboard Navigation**: Support arrow keys for layout switching
2. **Screen Reader**: Proper ARIA labels
3. **High Contrast**: Ensure sufficient color contrast
4. **Font Scaling**: Support dynamic font sizes
5. **Focus Management**: Clear focus indicators
