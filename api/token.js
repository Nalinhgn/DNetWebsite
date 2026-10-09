const { AccessToken } = require('livekit-server-sdk');

module.exports = async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');

  const name = (req.query.name || `guest-${Date.now()}`).slice(0, 40);
  const room = `taniya-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;

  const at = new AccessToken(
    process.env.LIVEKIT_API_KEY,
    process.env.LIVEKIT_API_SECRET,
    { identity: name, ttl: 7200 }
  );

  at.addGrant({
    roomJoin: true,
    room,
    canPublish: true,
    canSubscribe: true,
  });

  const token = await at.toJwt();

  res.status(200).json({ token, url: process.env.LIVEKIT_URL, room });
};
