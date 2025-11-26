// ===== ZELALAR - SERVICE WORKER =====
// Service Worker para funcionalidades offline e cache

const CACHE_NAME = 'zelalar-v1.0.0';
const STATIC_CACHE = 'zelalar-static-v1.0.0';
const DYNAMIC_CACHE = 'zelalar-dynamic-v1.0.0';

// Arquivos para cache estático
const STATIC_FILES = [
    '/',
    '/index.php',
    '/profissionais.php',
    '/listagem.php',
    '/css/style.css',
    '/js/main.js',
    '/js/particles.js',
    '/js/animations.js',
    '/js/utils.js',
    '/manifest.json',
    '/img/logo.png',
    '/img/hero-bg.jpg',
    '/img/icons/icon-192x192.png',
    '/img/icons/icon-512x512.png'
];

// Arquivos para cache dinâmico
const DYNAMIC_FILES = [
    '/api/profissionais',
    '/api/categorias'
];

// Estratégias de cache
const CACHE_STRATEGIES = {
    // Cache First para arquivos estáticos
    STATIC_FIRST: 'static-first',
    // Network First para dados dinâmicos
    NETWORK_FIRST: 'network-first',
    // Stale While Revalidate para recursos importantes
    STALE_WHILE_REVALIDATE: 'stale-while-revalidate'
};

// ===== INSTALAÇÃO =====
self.addEventListener('install', (event) => {
    console.log('🚀 ZelaLar Service Worker instalando...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('📦 Cache estático aberto');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('✅ Cache estático preenchido');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('❌ Erro ao preencher cache estático:', error);
            })
    );
});

// ===== ATIVAÇÃO =====
self.addEventListener('activate', (event) => {
    console.log('🔄 ZelaLar Service Worker ativando...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('🗑️ Removendo cache antigo:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('✅ Cache limpo com sucesso');
                return self.clients.claim();
            })
    );
});

// ===== INTERCEPTAÇÃO DE REQUESTS =====
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
   
    if (request.method !== 'GET') {
        return;
    }
    
    // Estratégia baseada no tipo de recurso
    if (isStaticResource(request)) {
        event.respondWith(handleStaticResource(request));
    } else if (isDynamicResource(request)) {
        event.respondWith(handleDynamicResource(request));
    } else if (isImage(request)) {
        event.respondWith(handleImage(request));
    } else {
        event.respondWith(handleDefault(request));
    }
});

// ===== ESTRATÉGIAS DE CACHE =====

/**
 * Estratégia Cache First para recursos estáticos
 */
async function handleStaticResource(request) {
    try {
        // Tentar buscar do cache primeiro
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Se não estiver no cache, buscar da rede
        const networkResponse = await fetch(request);
        
        // Armazenar no cache para uso futuro
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.error('❌ Erro ao buscar recurso estático:', error);
        
        // Fallback para página offline
        if (request.destination === 'document') {
            return getOfflinePage();
        }
        
        throw error;
    }
}

/**
 * Estratégia Network First para recursos dinâmicos
 */
async function handleDynamicResource(request) {
    try {
        // Tentar buscar da rede primeiro
        const networkResponse = await fetch(request);
        
        // Se sucesso, armazenar no cache
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('🌐 Rede indisponível, tentando cache...');
        
        // Fallback para cache
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Se não houver cache, retornar erro
        throw error;
    }
}

/**
 * Estratégia Stale While Revalidate para imagens
 */
async function handleImage(request) {
    const cache = await caches.open(DYNAMIC_CACHE);
    const cachedResponse = await cache.match(request);
    
    // Retornar cache imediatamente se disponível
    if (cachedResponse) {
        // Atualizar cache em background
        fetch(request).then(response => {
            if (response.ok) {
                cache.put(request, response);
            }
        }).catch(() => {
            // Ignorar erros de atualização
        });
        
        return cachedResponse;
    }
    
    // Se não estiver no cache, buscar da rede
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        // Retornar imagem placeholder se disponível
        const placeholderResponse = await cache.match('/img/placeholder.png');
        if (placeholderResponse) {
            return placeholderResponse;
        }
        
        throw error;
    }
}

/**
 * Estratégia padrão
 */
async function handleDefault(request) {
    try {
        const response = await fetch(request);
        return response;
    } catch (error) {
        console.error('❌ Erro na requisição:', error);
        throw error;
    }
}

// ===== FUNÇÕES AUXILIARES =====

/**
 * Verifica se é um recurso estático
 */
function isStaticResource(request) {
    const staticExtensions = ['.css', '.js', '.json', '.xml'];
    const url = new URL(request.url);
    
    return staticExtensions.some(ext => url.pathname.endsWith(ext)) ||
           STATIC_FILES.includes(url.pathname);
}

/**
 * Verifica se é um recurso dinâmico
 */
function isDynamicResource(request) {
    const url = new URL(request.url);
    return DYNAMIC_FILES.some(path => url.pathname.startsWith(path)) ||
           url.pathname.includes('/api/');
}

/**
 * Verifica se é uma imagem
 */
function isImage(request) {
    return request.destination === 'image';
}

/**
 * Retorna página offline
 */
async function getOfflinePage() {
    const cache = await caches.open(STATIC_CACHE);
    const offlineResponse = await cache.match('/offline.html');
    
    if (offlineResponse) {
        return offlineResponse;
    }
    
    // Criar página offline básica
    const offlineHTML = `
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>ZelaLar - Offline</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    text-align: center; 
                    padding: 50px; 
                    background: #1B4965; 
                    color: white; 
                }
                .offline-icon { font-size: 64px; margin: 20px; }
                .retry-btn { 
                    background: #5FA8D3; 
                    color: white; 
                    border: none; 
                    padding: 15px 30px; 
                    border-radius: 5px; 
                    cursor: pointer; 
                    margin: 20px; 
                }
            </style>
        </head>
        <body>
            <div class="offline-icon">📱</div>
            <h1>Você está offline</h1>
            <p>Algumas funcionalidades podem não estar disponíveis.</p>
            <button class="retry-btn" onclick="window.location.reload()">Tentar novamente</button>
        </body>
        </html>
    `;
    
    const response = new Response(offlineHTML, {
        headers: { 'Content-Type': 'text/html' }
    });
    
    // Armazenar no cache
    cache.put('/offline.html', response.clone());
    
    return response;
}

// ===== SINCRONIZAÇÃO EM BACKGROUND =====
self.addEventListener('sync', (event) => {
    console.log('🔄 Sincronização em background:', event.tag);
    
    if (event.tag === 'background-sync') {
        event.waitUntil(performBackgroundSync());
    }
});

async function performBackgroundSync() {
    try {
        // Sincronizar dados pendentes
        const pendingData = await getPendingData();
        
        for (const data of pendingData) {
            await syncData(data);
        }
        
        console.log('✅ Sincronização em background concluída');
    } catch (error) {
        console.error('❌ Erro na sincronização em background:', error);
    }
}

async function getPendingData() {
    // Implementar lógica para buscar dados pendentes
    return [];
}

async function syncData(data) {
    // Implementar lógica de sincronização
    console.log('Sincronizando dados:', data);
}

// ===== NOTIFICAÇÕES PUSH =====
self.addEventListener('push', (event) => {
    console.log('📱 Notificação push recebida');
    
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body || 'Nova notificação do ZelaLar',
            icon: '/img/icons/icon-192x192.png',
            badge: '/img/icons/icon-72x72.png',
            vibrate: [200, 100, 200],
            data: data.data || {},
            actions: data.actions || []
        };
        
        event.waitUntil(
            self.registration.showNotification(data.title || 'ZelaLar', options)
        );
    }
});

self.addEventListener('notificationclick', (event) => {
    console.log('👆 Notificação clicada');
    
    event.notification.close();
    
    if (event.action) {
        // Ação específica clicada
        handleNotificationAction(event.action, event.notification.data);
    } else {
        // Notificação clicada (ação padrão)
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

function handleNotificationAction(action, data) {
    switch (action) {
        case 'view':
            clients.openWindow('/listagem.php');
            break;
        case 'contact':
            clients.openWindow('https://wa.me/5511999999999');
            break;
        default:
            clients.openWindow('/');
    }
}

// ===== MENSAGENS =====
self.addEventListener('message', (event) => {
    console.log('💬 Mensagem recebida:', event.data);
    
    switch (event.data.type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
            
        case 'GET_VERSION':
            event.ports[0].postMessage({ version: CACHE_NAME });
            break;
            
        case 'CLEAR_CACHE':
            clearAllCaches();
            break;
            
        case 'UPDATE_CACHE':
            updateCache(event.data.files);
            break;
    }
});

async function clearAllCaches() {
    const cacheNames = await caches.keys();
    await Promise.all(
        cacheNames.map(name => caches.delete(name))
    );
    console.log('🗑️ Todos os caches foram limpos');
}

async function updateCache(files) {
    const cache = await caches.open(STATIC_CACHE);
    await Promise.all(
        files.map(file => cache.add(file))
    );
    console.log('🔄 Cache atualizado com novos arquivos');
}

// ===== MONITORAMENTO DE PERFORMANCE =====
self.addEventListener('fetch', (event) => {
    const startTime = performance.now();
    
    event.waitUntil(
        (async () => {
            try {
                await event.respondWith(handleRequest(event.request));
                const endTime = performance.now();
                const duration = endTime - startTime;
                
                // Registrar métricas de performance
                if (duration > 1000) {
                    console.warn('⚠️ Requisição lenta:', event.request.url, `${duration.toFixed(2)}ms`);
                }
            } catch (error) {
                console.error('❌ Erro na requisição:', error);
            }
        })()
    );
});

async function handleRequest(request) {
    // Implementar lógica de roteamento baseada na estratégia
    if (isStaticResource(request)) {
        return handleStaticResource(request);
    } else if (isDynamicResource(request)) {
        return handleDynamicResource(request);
    } else if (isImage(request)) {
        return handleImage(request);
    } else {
        return handleDefault(request);
    }
}

// ===== LOGS DE DEBUG =====
if (self.location.hostname === 'localhost') {
    console.log('🔧 ZelaLar Service Worker em modo desenvolvimento');
    
    // Expor funções para debug
    self.debug = {
        getCacheNames: () => caches.keys(),
        clearCache: (name) => caches.delete(name),
        getCacheSize: async (name) => {
            const cache = await caches.open(name);
            const keys = await cache.keys();
            return keys.length;
        }
    };
}
