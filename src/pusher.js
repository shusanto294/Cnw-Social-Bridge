import Pusher from 'pusher-js';

let pusherInstance = null;
let userChannel = null;
let pusherConnected = false;

/**
 * Get or create the shared Pusher instance.
 * Returns null if Pusher credentials are not configured.
 */
export function getPusher() {
  if (pusherInstance) return pusherInstance;

  const key = window.cnwData?.pusherKey;
  const cluster = window.cnwData?.pusherCluster || 'mt1';
  const wsHost = window.cnwData?.pusherHost || '';
  const wsPort = window.cnwData?.pusherPort || 443;

  if (!key) return null;

  const restUrl = window.cnwData?.restUrl || '';
  const nonce = window.cnwData?.nonce || '';

  // Enable Pusher logging in dev for debugging
  Pusher.logToConsole = true;

  const options = {
    cluster,
    authEndpoint: restUrl + '/pusher/auth',
    auth: {
      headers: {
        'X-WP-Nonce': nonce,
      },
    },
  };

  // Self-hosted Soketi / Laravel WebSockets
  if (wsHost) {
    const port = parseInt(wsPort) || 6001;
    options.wsHost = wsHost;
    options.wsPort = port;
    options.wssPort = port;
    options.forceTLS = (port === 443);
  }

  pusherInstance = new Pusher(key, options);

  pusherInstance.connection.bind('connected', () => {
    pusherConnected = true;
    console.log('[CNW] Pusher connected');
  });

  pusherInstance.connection.bind('failed', () => {
    pusherConnected = false;
    console.warn('[CNW] Pusher connection failed — falling back to polling');
  });

  pusherInstance.connection.bind('disconnected', () => {
    pusherConnected = false;
  });

  return pusherInstance;
}

/**
 * Subscribe to the current user's private channel.
 * Returns the channel, or null if Pusher is not configured.
 */
export function getUserChannel() {
  if (userChannel) return userChannel;

  const pusher = getPusher();
  if (!pusher) return null;

  const userId = window.cnwData?.currentUser?.id;
  if (!userId) return null;

  userChannel = pusher.subscribe('private-user-' + userId);

  userChannel.bind('pusher:subscription_error', (err) => {
    console.warn('[CNW] Pusher channel auth failed:', err);
    pusherConnected = false;
  });

  userChannel.bind('pusher:subscription_succeeded', () => {
    pusherConnected = true;
    console.log('[CNW] Pusher channel subscribed');
  });

  return userChannel;
}

/**
 * Check if Pusher credentials are configured.
 */
export function isPusherEnabled() {
  return !!window.cnwData?.pusherKey;
}

/**
 * Check if Pusher is actually connected and the channel is subscribed.
 */
export function isPusherConnected() {
  return pusherConnected;
}
