/**
 * OpenGestiCourrier V1.3 - Dynamic Document & Stamp Live Preview with Multi-Page Navigation, Zoom, Drag & Drop & Resizing
 */

let currentZoomLevel = 1.0;
let isDraggingStamp = false;
let isResizingStamp = false;

let stampDragStartX = 0;
let stampDragStartY = 0;
let initialStampLeft = 0;
let initialStampTop = 0;

let stampResizeStartX = 0;
let stampCurrentFontSize = 12;

let pdfDocInstance = null;
let currentPdfPage = 1;
let totalPdfPages = 1;

function initStampDocPreview() {
    const previewContainer = document.getElementById('live-stamp-preview-container');
    if (!previewContainer) return;

    const fileInputs = document.querySelectorAll('input[type="file"][name^="document"]');
    const dateInput = document.getElementById('date');
    const numOrdreInput = document.getElementById('num_ordre');
    const categorieSelect = document.getElementById('categorie_courrier');

    if (window.pdfjsLib) {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    }

    fileInputs.forEach((input) => {
        input.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                currentZoomLevel = 1.0;
                renderFileWithStamp(this.files[0], this.name);
            }
        });
    });

    if (dateInput) dateInput.addEventListener('change', updateDocStampText);
    if (numOrdreInput) numOrdreInput.addEventListener('input', updateDocStampText);
    if (categorieSelect) categorieSelect.addEventListener('change', updateDocStampText);

    setupStampGlobalDragListeners();
}

function changePreviewZoom(delta) {
    currentZoomLevel += delta;
    if (currentZoomLevel < 0.5) currentZoomLevel = 0.5;
    if (currentZoomLevel > 2.5) currentZoomLevel = 2.5;
    applyPreviewZoom();
}

function resetPreviewZoom() {
    currentZoomLevel = 1.0;
    applyPreviewZoom();
}

function applyPreviewZoom() {
    const sheet = document.getElementById('stamp-doc-sheet');
    const indicator = document.getElementById('zoom-level-indicator');
    if (sheet) {
        sheet.style.transform = `scale(${currentZoomLevel})`;
    }
    if (indicator) {
        indicator.textContent = Math.round(currentZoomLevel * 100) + '%';
    }
}

function changeStampFontSize(delta) {
    stampCurrentFontSize += delta;
    if (stampCurrentFontSize < 8) stampCurrentFontSize = 8;
    if (stampCurrentFontSize > 28) stampCurrentFontSize = 28;
    applyStampFontSize();
}

function applyStampFontSize() {
    const stampOverlay = document.getElementById('doc-stamp-overlay');
    if (stampOverlay) {
        stampOverlay.style.fontSize = stampCurrentFontSize + 'px';
    }
}

function changePdfPage(delta) {
    if (!pdfDocInstance) return;
    const newPage = currentPdfPage + delta;
    if (newPage >= 1 && newPage <= totalPdfPages) {
        currentPdfPage = newPage;
        renderPdfPage(currentPdfPage);
    }
}

function setupStampGlobalDragListeners() {
    document.addEventListener('mousedown', function(e) {
        const handle = e.target.closest('.stamp-resize-handle');
        if (handle) {
            isResizingStamp = true;
            stampResizeStartX = e.clientX;
            const stampOverlay = document.getElementById('doc-stamp-overlay');
            if (stampOverlay) {
                const computed = window.getComputedStyle(stampOverlay);
                stampCurrentFontSize = parseFloat(computed.fontSize) || 12;
            }
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        const stampOverlay = e.target.closest('#doc-stamp-overlay');
        const sheet = document.getElementById('stamp-doc-sheet');
        if (!stampOverlay || !sheet) return;

        isDraggingStamp = true;
        stampOverlay.style.cursor = 'grabbing';
        stampOverlay.style.transition = 'none';

        stampDragStartX = e.clientX;
        stampDragStartY = e.clientY;

        const stampRect = stampOverlay.getBoundingClientRect();
        const sheetRect = sheet.getBoundingClientRect();
        const zoom = currentZoomLevel || 1.0;

        initialStampLeft = (stampRect.left - sheetRect.left) / zoom;
        initialStampTop = (stampRect.top - sheetRect.top) / zoom;

        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (isResizingStamp) {
            const deltaX = (e.clientX - stampResizeStartX) / (currentZoomLevel || 1.0);
            let newFontSize = stampCurrentFontSize + (deltaX * 0.04);
            if (newFontSize < 8) newFontSize = 8;
            if (newFontSize > 28) newFontSize = 28;

            const stampOverlay = document.getElementById('doc-stamp-overlay');
            if (stampOverlay) {
                stampOverlay.style.fontSize = newFontSize + 'px';
            }
            return;
        }

        if (!isDraggingStamp) return;

        const stampOverlay = document.getElementById('doc-stamp-overlay');
        const sheet = document.getElementById('stamp-doc-sheet');
        if (!stampOverlay || !sheet) return;

        const zoom = currentZoomLevel || 1.0;
        const deltaX = (e.clientX - stampDragStartX) / zoom;
        const deltaY = (e.clientY - stampDragStartY) / zoom;

        let newLeft = initialStampLeft + deltaX;
        let newTop = initialStampTop + deltaY;

        const maxLeft = sheet.clientWidth - stampOverlay.offsetWidth;
        const maxTop = sheet.clientHeight - stampOverlay.offsetHeight;

        if (newLeft < 0) newLeft = 0;
        if (newTop < 0) newTop = 0;
        if (newLeft > maxLeft) newLeft = maxLeft;
        if (newTop > maxTop) newTop = maxTop;

        stampOverlay.style.top = newTop + 'px';
        stampOverlay.style.left = newLeft + 'px';
        stampOverlay.style.right = 'auto';
        stampOverlay.style.bottom = 'auto';
        stampOverlay.style.transform = 'none';
    });

    document.addEventListener('mouseup', function() {
        if (isResizingStamp) {
            isResizingStamp = false;
            const stampOverlay = document.getElementById('doc-stamp-overlay');
            if (stampOverlay) {
                const computed = window.getComputedStyle(stampOverlay);
                stampCurrentFontSize = parseFloat(computed.fontSize) || 12;
            }
        }
        if (isDraggingStamp) {
            isDraggingStamp = false;
            const stampOverlay = document.getElementById('doc-stamp-overlay');
            if (stampOverlay) {
                stampOverlay.style.cursor = 'grab';
                stampOverlay.style.transition = 'all 0.2s ease';
            }
        }
    });
}

async function renderFileWithStamp(file, inputName) {
    const previewContainer = document.getElementById('live-stamp-preview-container');
    const previewCanvas = document.getElementById('stamp-doc-canvas');
    const stampOverlay = document.getElementById('doc-stamp-overlay');
    const docInfoBadge = document.getElementById('doc-preview-info-badge');
    const pageNavControls = document.getElementById('pdf-page-nav-controls');

    if (!previewContainer || !previewCanvas || !stampOverlay) return;

    previewContainer.style.display = 'block';

    const fileName = file.name;
    const fileSize = (file.size / 1024).toFixed(1) + ' Ko';
    const fileExt = fileName.split('.').pop().toLowerCase();

    if (docInfoBadge) {
        docInfoBadge.innerHTML = `<i class="fas fa-file-alt"></i> Aperçu du courrier avec tampon apposé : <strong>${escapeHtml(fileName)}</strong> (${fileSize})`;
    }

    if (pageNavControls) {
        pageNavControls.style.display = 'none';
    }

    pdfDocInstance = null;
    currentPdfPage = 1;
    totalPdfPages = 1;

    previewCanvas.style.backgroundImage = 'none';
    previewCanvas.innerHTML = '';
    previewCanvas.appendChild(stampOverlay);

    if (['jpg', 'jpeg', 'png', 'webp'].includes(fileExt)) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewCanvas.style.backgroundImage = `url("${e.target.result}")`;
            previewCanvas.style.backgroundSize = 'contain';
            previewCanvas.style.backgroundRepeat = 'no-repeat';
            previewCanvas.style.backgroundPosition = 'center';
        };
        reader.readAsDataURL(file);
    } else if (fileExt === 'pdf') {
        if (window.pdfjsLib) {
            try {
                const arrayBuffer = await file.arrayBuffer();
                pdfDocInstance = await window.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                totalPdfPages = pdfDocInstance.numPages;
                currentPdfPage = 1;

                if (pageNavControls && totalPdfPages > 1) {
                    pageNavControls.style.display = 'flex';
                    updatePageNavIndicators();
                }

                await renderPdfPage(currentPdfPage);
            } catch (err) {
                console.warn('PDF.js render fallback to IFrame:', err);
                renderPdfIframeFallback(file, previewCanvas);
            }
        } else {
            renderPdfIframeFallback(file, previewCanvas);
        }
    } else {
        const genMock = document.createElement('div');
        genMock.style.textAlign = 'center';
        genMock.style.padding = '60px 20px';
        genMock.innerHTML = `
            <i class="fas fa-file" style="font-size: 4.5rem; color: #2563eb; margin-bottom: 12px;"></i>
            <div style="font-weight: 700; color: #334155; font-size: 1.1rem;">${escapeHtml(fileName)}</div>
        `;
        previewCanvas.appendChild(genMock);
    }

    applyPreviewZoom();
    applyStampStyles();
    updateDocStampText();
}

async function renderPdfPage(pageNum) {
    if (!pdfDocInstance) return;
    const previewCanvas = document.getElementById('stamp-doc-canvas');
    const stampOverlay = document.getElementById('doc-stamp-overlay');
    if (!previewCanvas || !stampOverlay) return;

    try {
        const page = await pdfDocInstance.getPage(pageNum);

        const targetWidth = 595;
        const unscaledViewport = page.getViewport({ scale: 1.0 });
        const scale = targetWidth / unscaledViewport.width;
        const viewport = page.getViewport({ scale: scale });

        const oldCanvases = previewCanvas.querySelectorAll('canvas');
        oldCanvases.forEach(c => c.remove());

        const canvas = document.createElement('canvas');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        canvas.style.borderRadius = '4px';
        canvas.style.display = 'block';

        const context = canvas.getContext('2d');
        await page.render({ canvasContext: context, viewport: viewport }).promise;

        previewCanvas.insertBefore(canvas, stampOverlay);
        updatePageNavIndicators();
    } catch (err) {
        console.error('Erreur rendu page PDF:', err);
    }
}

function updatePageNavIndicators() {
    const pageNumSpan = document.getElementById('pdf-page-num');
    const pageCountSpan = document.getElementById('pdf-page-count');
    const prevBtn = document.getElementById('btn-pdf-prev');
    const nextBtn = document.getElementById('btn-pdf-next');

    if (pageNumSpan) pageNumSpan.textContent = currentPdfPage;
    if (pageCountSpan) pageCountSpan.textContent = totalPdfPages;

    if (prevBtn) prevBtn.disabled = (currentPdfPage <= 1);
    if (nextBtn) nextBtn.disabled = (currentPdfPage >= totalPdfPages);
}

function renderPdfIframeFallback(file, previewCanvas) {
    const blobUrl = URL.createObjectURL(file);
    const iframe = document.createElement('iframe');
    iframe.src = blobUrl + '#toolbar=0&navpanes=0&view=FitH';
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.style.border = 'none';
    iframe.style.pointerEvents = 'none';
    previewCanvas.appendChild(iframe);
}

function applyStampStyles() {
    const stampOverlay = document.getElementById('doc-stamp-overlay');
    if (!stampOverlay) return;

    const cfg = window.STAMP_CONFIG || {};
    if (cfg.tampon_active === '0') {
        stampOverlay.style.display = 'none';
        return;
    } else {
        stampOverlay.style.display = 'block';
    }

    const position = cfg.tampon_position || 'top-right';
    const couleur = cfg.tampon_couleur || '#2563eb';
    const opacite = cfg.tampon_opacite ? (parseFloat(cfg.tampon_opacite) / 100) : 0.85;
    const taille = cfg.tampon_taille || 'medium';
    const bordure = cfg.tampon_bordure || 'double';

    stampOverlay.style.top = 'auto';
    stampOverlay.style.bottom = 'auto';
    stampOverlay.style.left = 'auto';
    stampOverlay.style.right = 'auto';
    stampOverlay.style.transform = 'none';

    if (position === 'top-right') {
        stampOverlay.style.top = '20px';
        stampOverlay.style.right = '20px';
    } else if (position === 'top-left') {
        stampOverlay.style.top = '20px';
        stampOverlay.style.left = '20px';
    } else if (position === 'bottom-right') {
        stampOverlay.style.bottom = '20px';
        stampOverlay.style.right = '20px';
    } else if (position === 'bottom-left') {
        stampOverlay.style.bottom = '20px';
        stampOverlay.style.left = '20px';
    } else if (position === 'center') {
        stampOverlay.style.top = '50%';
        stampOverlay.style.left = '50%';
        stampOverlay.style.transform = 'translate(-50%, -50%)';
    }

    let borderStyle = '2.5px solid ' + couleur;
    if (bordure === 'double') borderStyle = '4.5px double ' + couleur;
    if (bordure === 'dashed') borderStyle = '2.5px dashed ' + couleur;
    if (bordure === 'rounded') borderStyle = '2.5px solid ' + couleur;
    if (bordure === 'none') borderStyle = 'none';

    stampCurrentFontSize = 12;
    if (taille === 'small') stampCurrentFontSize = 10;
    if (taille === 'large') stampCurrentFontSize = 14;
    if (taille === 'xlarge') stampCurrentFontSize = 16;

    stampOverlay.style.border = borderStyle;
    stampOverlay.style.color = couleur;
    stampOverlay.style.opacity = opacite;
    stampOverlay.style.fontSize = stampCurrentFontSize + 'px';
    stampOverlay.style.borderRadius = (bordure === 'rounded') ? '8px' : '0px';
    stampOverlay.style.pointerEvents = 'auto';
    stampOverlay.style.cursor = 'grab';
    stampOverlay.title = 'Cliquez et glissez pour déplacer le tampon. Utilisez la poignée en bas à droite pour le redimensionner.';
}

function updateDocStampText() {
    const stampOverlay = document.getElementById('doc-stamp-overlay');
    if (!stampOverlay) return;

    const cfg = window.STAMP_CONFIG || {};

    if (cfg.tampon_active === '0') {
        stampOverlay.style.display = 'none';
        return;
    } else {
        stampOverlay.style.display = 'block';
    }

    const dateInput = document.getElementById('date');
    const numOrdreInput = document.getElementById('num_ordre');
    const categorieSelect = document.getElementById('categorie_courrier');
    const fluxInput = document.getElementById('flux');
    const flux = fluxInput ? fluxInput.value : 'ARRIVE';

    const customText = (cfg.tampon_texte_custom !== undefined && cfg.tampon_texte_custom !== '')
        ? cfg.tampon_texte_custom
        : (flux === 'ARRIVE' ? 'ARRIVÉE - COURRIER' : 'DÉPART - COURRIER');

    const showOrg = ((cfg.tampon_show_org ?? '1') === '1');
    const showNum = ((cfg.tampon_show_num ?? '1') === '1');
    const showDate = ((cfg.tampon_show_date ?? '1') === '1');
    const showCat = ((cfg.tampon_show_categorie ?? '1') === '1');
    const disposition = cfg.tampon_disposition || 'ligne';

    const orgName = cfg.raison_sociale || 'Mairie de Conques-sur-Orbiel';

    let dateVal = dateInput ? dateInput.value : '';
    if (dateVal) {
        const parts = dateVal.split('-');
        if (parts.length === 3) dateVal = `${parts[2]}/${parts[1]}/${parts[0]}`;
    } else {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        dateVal = `${dd}/${mm}/${yyyy}`;
    }

    const numOrdre = (numOrdreInput && numOrdreInput.value) ? numOrdreInput.value : '----';
    let catText = '---';
    if (categorieSelect && categorieSelect.selectedIndex >= 0) {
        catText = categorieSelect.options[categorieSelect.selectedIndex].text;
    }

    const datePrefix = (flux === 'ARRIVE' ? 'Reçu le : ' : 'Parti le : ');

    const items = [];
    if (customText) items.push(`<strong>${escapeHtml(customText.toUpperCase())}</strong>`);
    if (showOrg && orgName) items.push(escapeHtml(orgName));
    if (showNum) items.push(`N° Ordre : <strong>${escapeHtml(numOrdre)}</strong>`);
    if (showDate) items.push(`${datePrefix}<strong>${escapeHtml(dateVal)}</strong>`);
    if (showCat && catText && catText !== '---') items.push(`Catégorie : <strong>${escapeHtml(catText)}</strong>`);

    let html = '<div class="stamp-content-wrapper" style="';
    if (disposition === 'ligne') {
        html += 'display: flex; flex-wrap: wrap; align-items: center; gap: 8px; justify-content: center; white-space: nowrap;">';
        html += items.join('<span style="opacity: 0.6; margin: 0 4px;">|</span>');
    } else {
        html += 'display: flex; flex-direction: column; align-items: center; gap: 2px; text-align: center;">';
        html += items.map(it => `<div>${it}</div>`).join('');
    }
    html += '</div>';

    html += '<div class="stamp-resize-handle" title="Glissez horizontalement pour agrandir/réduire le tampon"><i class="fas fa-expand-alt"></i></div>';

    stampOverlay.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', initStampDocPreview);
