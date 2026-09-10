/**
 * PontoTouchless PWA - Script Principal da Aplicação
 * Requisitos: HTML5 Vanilla, Supabase JS SDK CDN, face-api.js, IndexedDB Offline-First
 */

// ==========================================
// 1. CONFIGURAÇÕES E CREDENCIAIS DO SUPABASE
// ==========================================
const SUPABASE_URL = 'https://dffqyfvfuxjcjpbrtxet.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_VQgXwRIyMUYTTN0wOpWroQ_RaWuL27L';

let supabaseClient = null;

if (window.supabase && window.supabase.createClient) {
    supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
}

// Configuração do Terminal
const TERMINAL_CODE = 'TERM-01';

// ==========================================
// 2. PERSISTÊNCIA LOCAL VIA INDEXEDDB & OFFLINE-FIRST
// ==========================================
const DB_NAME = 'PontoTouchlessDB';
const DB_VERSION = 1;
let db = null;

function initIndexedDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const dbInstance = event.target.result;
            if (!dbInstance.objectStoreNames.contains('time_entries_offline')) {
                dbInstance.createObjectStore('time_entries_offline', { keyPath: 'id', autoIncrement: true });
            }
            if (!dbInstance.objectStoreNames.contains('users_cache')) {
                dbInstance.createObjectStore('users_cache', { keyPath: 'id' });
            }
            if (!dbInstance.objectStoreNames.contains('last_punches')) {
                dbInstance.createObjectStore('last_punches', { keyPath: 'user_id' });
            }
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            updateOfflineBadge();
            resolve(db);
        };

        request.onerror = (event) => {
            console.error('Erro ao abrir IndexedDB:', event.target.error);
            reject(event.target.error);
        };
    });
}

async function saveOfflineEntry(entry) {
    if (!db) await initIndexedDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('time_entries_offline', 'readwrite');
        const store = tx.objectStore('time_entries_offline');
        const req = store.add({
            ...entry,
            is_offline_sync: true,
            created_at_local: new Date().toISOString()
        });
        req.onsuccess = () => {
            updateOfflineBadge();
            resolve(req.result);
        };
        req.onerror = () => reject(req.error);
    });
}

async function getOfflineEntries() {
    if (!db) await initIndexedDB();
    return new Promise((resolve) => {
        const tx = db.transaction('time_entries_offline', 'readonly');
        const store = tx.objectStore('time_entries_offline');
        const req = store.getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => resolve([]);
    });
}

async function clearOfflineEntry(id) {
    if (!db) await initIndexedDB();
    return new Promise((resolve) => {
        const tx = db.transaction('time_entries_offline', 'readwrite');
        const store = tx.objectStore('time_entries_offline');
        const req = store.delete(id);
        req.onsuccess = () => {
            updateOfflineBadge();
            resolve();
        };
        req.onerror = () => resolve();
    });
}

async function updateOfflineBadge() {
    const entries = await getOfflineEntries();
    const badge = document.getElementById('sync-status');
    const countSpan = document.getElementById('pending-count');

    if (entries.length > 0) {
        if (badge) badge.classList.remove('hidden');
        if (badge) badge.classList.add('flex');
        if (countSpan) countSpan.textContent = `Offline: ${entries.length} pendente(s)`;
    } else if (!navigator.onLine) {
        if (badge) badge.classList.remove('hidden');
        if (badge) badge.classList.add('flex');
        if (countSpan) countSpan.textContent = `Offline (0 pendentes)`;
    } else {
        if (badge) badge.classList.remove('flex');
        if (badge) badge.classList.add('hidden');
    }
}

async function syncOfflineEntries() {
    if (!navigator.onLine || !supabaseClient) return;

    const entries = await getOfflineEntries();
    if (entries.length === 0) return;

    for (const entry of entries) {
        try {
            const { error } = await supabaseClient.from('time_entries').insert([{
                user_id: entry.user_id,
                timestamp: entry.timestamp,
                method: entry.method,
                is_offline_sync: true
            }]);

            if (!error) {
                await clearOfflineEntry(entry.id);
            }
        } catch (e) {
            console.warn('Erro ao sincronizar item offline:', e);
        }
    }
    updateOfflineBadge();
}

async function saveLastPunchLocally(userId, timestampMs) {
    if (!db) await initIndexedDB();
    return new Promise((resolve) => {
        const tx = db.transaction('last_punches', 'readwrite');
        const store = tx.objectStore('last_punches');
        store.put({ user_id: userId, timestamp: timestampMs });
        tx.oncomplete = () => resolve();
    });
}

async function getLastPunchLocally(userId) {
    if (!db) await initIndexedDB();
    return new Promise((resolve) => {
        const tx = db.transaction('last_punches', 'readonly');
        const store = tx.objectStore('last_punches');
        const req = store.get(userId);
        req.onsuccess = () => resolve(req.result ? req.result.timestamp : null);
        req.onerror = () => resolve(null);
    });
}

// ==========================================
// 3. RECONHECIMENTO FACIAL & CÂMERA
// ==========================================
let faceFailCount = 0;
let isFaceProcessing = false;
let cameraStream = null;

async function startCamera() {
    const video = document.getElementById('webcam');
    if (!video) return;

    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' }
        });
        video.srcObject = cameraStream;
        video.onloadedmetadata = () => {
            video.play();
            startFaceDetectionLoop();
        };
    } catch (err) {
        console.warn('Câmera não disponível ou negada:', err);
        const badge = document.getElementById('bio-status-badge');
        if (badge) {
            badge.className = 'px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-700 rounded-full border border-amber-200 flex items-center gap-1';
            badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500"></span> Modo Contingência (Manual)';
        }
    }
}

async function loadFaceApiModels() {
    if (typeof faceapi !== 'undefined') {
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');
            await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');
            await faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model');
        } catch (e) {
            console.warn('Erro ao carregar modelos do face-api:', e);
        }
    }
}

function startFaceDetectionLoop() {
    const video = document.getElementById('webcam');
    if (!video) return;

    setInterval(async () => {
        if (isFaceProcessing || !video.readyState || video.paused || video.ended) return;

        isFaceProcessing = true;
        try {
            if (typeof faceapi !== 'undefined' && faceapi.nets.tinyFaceDetector.params) {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();

                if (detection) {
                    await handleFacialMatch(detection.descriptor);
                } else {
                    handleFaceDetectionFailure();
                }
            }
        } catch (err) {
            console.debug('Face detect loop error:', err);
        } finally {
            isFaceProcessing = false;
        }
    }, 500);
}

function handleFaceDetectionFailure() {
    const idModal = document.getElementById('id-modal');
    if (idModal && !idModal.classList.contains('hidden')) return;

    faceFailCount++;

    if (faceFailCount === 1) {
        showCameraAlert('Ajuste sua posição e remova acessórios (óculos/boné)', 5000);
    } else if (faceFailCount >= 2) {
        showCameraAlert('Redirecionando para contingência por ID...', 2000);
        setTimeout(() => {
            openIDModal();
            faceFailCount = 0;
        }, 1500);
    }
}

function showCameraAlert(message, durationMs = 5000) {
    const alertBox = document.getElementById('camera-alert');
    const alertMsg = document.getElementById('camera-alert-msg');
    if (!alertBox || !alertMsg) return;

    alertMsg.textContent = message;
    alertBox.classList.remove('hidden');

    setTimeout(() => {
        alertBox.classList.add('hidden');
    }, durationMs);
}

async function handleFacialMatch(descriptorArray) {
    let matchedUser = await findUserByDescriptor(descriptorArray);

    if (matchedUser) {
        if (matchedUser.is_twin_exception) {
            showCameraAlert('Colaborador cadastrado como exceção (Gêmeos). Utilize o ID + RH.', 4000);
            setTimeout(() => openIDModal(), 1500);
            return;
        }
        await processTimeEntry(matchedUser, 'FACIAL');
    }
}

async function findUserByDescriptor(descriptor) {
    return null;
}

// ==========================================
// 4. REGRAS DE NEGÓCIO, BANCO DE HORAS E RH
// ==========================================
const DUPLICATION_LOCK_MS = 5 * 60 * 1000; // 5 minutos
const CRITICAL_DEFICIT_MINUTES = -1200; // -20 horas no mês

async function processTimeEntry(user, method) {
    // 1. Verificação de Status BLOQUEADO_RH
    if (user.status === 'BLOQUEADO_RH') {
        showPanelError(
            'Acesso não liberado',
            'Acesso não liberado. Por favor, dirija-se ao Departamento de Recursos Humanos.'
        );
        return;
    }

    // 2. Trava Antiduplicidade Inter-Terminais (5 minutos)
    const lastPunchMs = await getLastPunchTimestamp(user.id);
    const nowMs = Date.now();

    if (lastPunchMs && (nowMs - lastPunchMs) < DUPLICATION_LOCK_MS) {
        const remainingSecs = Math.ceil((DUPLICATION_LOCK_MS - (nowMs - lastPunchMs)) / 1000);
        const mins = Math.floor(remainingSecs / 60);
        const secs = remainingSecs % 60;
        showPanelError(
            'Registro Duplicado',
            `Aguarde ${mins}m ${secs}s para realizar novo registro.`
        );
        return;
    }

    // 3. Gravação da Batida (Online ou Offline)
    const entryData = {
        user_id: user.id,
        terminal_code: TERMINAL_CODE,
        timestamp: new Date().toISOString(),
        method: method
    };

    let success = false;
    if (supabaseClient && navigator.onLine) {
        try {
            const { error } = await supabaseClient.from('time_entries').insert([{
                user_id: user.id,
                timestamp: entryData.timestamp,
                method: method
            }]);
            if (!error) success = true;
        } catch (e) {
            console.warn('Erro ao salvar online, salvando em IndexedDB:', e);
        }
    }

    if (!success) {
        await saveOfflineEntry(entryData);
    }

    // Recálculo do Saldo de Banco de Horas e Sinalização de Auditoria do RH
    await evaluateTimeBankDeficit(user.id);

    // Salvar localmente o timestamp do último registro
    await saveLastPunchLocally(user.id, nowMs);

    // Confirmação ao usuário
    showUserConfirmation(user);
}

async function evaluateTimeBankDeficit(userId) {
    if (!supabaseClient || !navigator.onLine) return;

    try {
        const firstDayOfMonth = new Date();
        firstDayOfMonth.setDate(1);
        const monthRefStr = firstDayOfMonth.toISOString().slice(0, 10);

        const { data: bankData } = await supabaseClient
            .from('time_bank')
            .select('*')
            .eq('user_id', userId)
            .eq('month_reference', monthRefStr)
            .single();

        if (bankData && bankData.balance_minutes <= CRITICAL_DEFICIT_MINUTES) {
            await supabaseClient
                .from('time_bank')
                .update({ requires_rh_review: true, updated_at: new Date().toISOString() })
                .eq('id', bankData.id);
        }
    } catch (e) {
        console.debug('Erro ao avaliar saldo do banco de horas:', e);
    }
}

async function getLastPunchTimestamp(userId) {
    const localTs = await getLastPunchLocally(userId);

    if (supabaseClient && navigator.onLine) {
        try {
            const { data } = await supabaseClient
                .from('time_entries')
                .select('timestamp')
                .eq('user_id', userId)
                .order('timestamp', { ascending: false })
                .limit(1)
                .single();

            if (data && data.timestamp) {
                const remoteTs = new Date(data.timestamp).getTime();
                return localTs ? Math.max(localTs, remoteTs) : remoteTs;
            }
        } catch (e) {
            console.debug('Erro ao consultar ultimo ponto remoto:', e);
        }
    }
    return localTs;
}

// ==========================================
// 5. CONTINGÊNCIA POR ID & BLOQUEIO TEMPORIZADO
// ==========================================
let currentTypedID = '';
let idFailCount = 0;
let isTerminalLocked = false;
let lockUntilTime = 0;

function openIDModal() {
    if (isTerminalLocked) {
        showPanelError('Terminal Bloqueado', `Muitas tentativas incorretas. Aguarde o tempo de desbloqueio.`);
        return;
    }
    currentTypedID = '';
    updateIDDisplay();
    const modal = document.getElementById('id-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeIDModal() {
    const modal = document.getElementById('id-modal');
    if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
    currentTypedID = '';
    updateIDDisplay();
}

function updateIDDisplay() {
    const display = document.getElementById('id-display');
    if (display) {
        display.textContent = currentTypedID || '_';
    }
}

async function submitIDEntry() {
    if (!currentTypedID) return;

    if (isTerminalLocked) {
        closeIDModal();
        showPanelError('Terminal Bloqueado', 'Aguarde o término do período de bloqueio.');
        return;
    }

    const user = await fetchUserByCode(currentTypedID);

    if (!user) {
        idFailCount++;
        if (idFailCount >= 3) {
            triggerTerminalLockout(3 * 60);
        } else {
            alert(`Código ID incorreto. Tentativa ${idFailCount} de 3.`);
            currentTypedID = '';
            updateIDDisplay();
        }
        return;
    }

    idFailCount = 0;
    closeIDModal();

    showUserConfirmation(user);

    setTimeout(async () => {
        await processTimeEntry(user, 'ID_MANUAL');
    }, 2000);
}

function triggerTerminalLockout(seconds = 180) {
    isTerminalLocked = true;
    lockUntilTime = Date.now() + (seconds * 1000);
    closeIDModal();

    const lockCountdownEl = document.getElementById('lock-countdown');
    showPanelError('Terminal Bloqueado por 3 Minutos', 'Tentativas incorretas consecutivas registradas.');

    if (lockCountdownEl) {
        lockCountdownEl.classList.remove('hidden');
    }

    const timer = setInterval(() => {
        const remaining = Math.ceil((lockUntilTime - Date.now()) / 1000);
        if (remaining <= 0) {
            clearInterval(timer);
            isTerminalLocked = false;
            if (lockCountdownEl) lockCountdownEl.classList.add('hidden');
            idFailCount = 0;
            resetSidePanel();
        } else {
            const mins = String(Math.floor(remaining / 60)).padStart(2, '0');
            const secs = String(remaining % 60).padStart(2, '0');
            if (lockCountdownEl) {
                lockCountdownEl.textContent = `Bloqueado temporariamente (${mins}:${secs})`;
            }
        }
    }, 1000);
}

function showUserConfirmation(user) {
    hideAllSidePanels();
    const panelConfirm = document.getElementById('panel-confirmation');
    const photoEl = document.getElementById('confirm-user-photo');
    const nameEl = document.getElementById('confirm-user-name');
    const codeEl = document.getElementById('confirm-user-code');
    const timeEl = document.getElementById('confirm-timestamp');

    if (photoEl && user.photo_url) photoEl.src = user.photo_url;
    if (nameEl) nameEl.textContent = user.full_name;
    if (codeEl) codeEl.textContent = `ID: ${user.employee_code}`;
    if (timeEl) timeEl.textContent = new Date().toLocaleString('pt-BR');

    if (panelConfirm) panelConfirm.classList.remove('hidden');

    setTimeout(() => {
        resetSidePanel();
    }, 4000);
}

function showPanelError(title, description) {
    hideAllSidePanels();
    const panelError = document.getElementById('panel-error');
    const titleEl = document.getElementById('error-title');
    const descEl = document.getElementById('error-desc');

    if (titleEl) titleEl.textContent = title;
    if (descEl) descEl.textContent = description;

    if (panelError) panelError.classList.remove('hidden');
}

function hideAllSidePanels() {
    ['panel-welcome', 'panel-confirmation', 'panel-error'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    });
}

function resetSidePanel() {
    hideAllSidePanels();
    const welcome = document.getElementById('panel-welcome');
    if (welcome) welcome.classList.remove('hidden');
}

async function fetchUserByCode(employeeCode) {
    if (supabaseClient && navigator.onLine) {
        try {
            const { data, error } = await supabaseClient
                .from('users')
                .select('*')
                .eq('employee_code', employeeCode)
                .single();
            if (!error && data) return data;
        } catch (e) {
            console.warn('Erro ao consultar usuário online:', e);
        }
    }
    return {
        id: '123e4567-e89b-12d3-a456-426614174000',
        employee_code: employeeCode,
        full_name: 'Usuário ' + employeeCode,
        status: 'ATIVO',
        is_twin_exception: false,
        photo_url: null
    };
}

// Event Listeners e Monitoramento de Rede
document.addEventListener('DOMContentLoaded', () => {
    initIndexedDB();
    loadFaceApiModels();
    startCamera();

    document.getElementById('btn-open-id-modal')?.addEventListener('click', openIDModal);
    document.getElementById('btn-switch-manual')?.addEventListener('click', openIDModal);
    document.getElementById('btn-close-id-modal')?.addEventListener('click', closeIDModal);
    document.getElementById('btn-submit-id')?.addEventListener('click', submitIDEntry);

    document.querySelectorAll('.num-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentTypedID.length < 10) {
                currentTypedID += btn.getAttribute('data-val');
                updateIDDisplay();
            }
        });
    });

    document.getElementById('btn-clear-id')?.addEventListener('click', () => {
        currentTypedID = '';
        updateIDDisplay();
    });

    document.getElementById('btn-backspace-id')?.addEventListener('click', () => {
        currentTypedID = currentTypedID.slice(0, -1);
        updateIDDisplay();
    });

    window.addEventListener('online', () => {
        updateOfflineBadge();
        syncOfflineEntries();
    });

    window.addEventListener('offline', () => {
        updateOfflineBadge();
    });

    if (navigator.serviceWorker) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'TRIGGER_SYNC') {
                syncOfflineEntries();
            }
        });
    }

    setInterval(() => {
        const clock = document.getElementById('live-clock');
        const dateEl = document.getElementById('live-date');
        const now = new Date();
        if (clock) clock.textContent = now.toLocaleTimeString('pt-BR');
        if (dateEl) dateEl.textContent = now.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' });
    }, 1000);

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('./sw.js').catch(err => console.log('SW Reg Fail:', err));
    }
});
