/** @type {import('next').NextConfig} */
const nextConfig = {
  typescript: {
    ignoreBuildErrors: true,
  },
  images: {
    unoptimized: true,
  },
  // Next.js 16 defaults to Turbopack; we use webpack in Docker (poll for bind mounts).
  turbopack: {},
  // When running the frontend standalone (without Nginx), proxy API calls to Laravel.
  async rewrites() {
    const apiUrl = process.env.API_URL ?? "http://localhost:8000"
    return [
      { source: "/api/:path*", destination: `${apiUrl}/api/:path*` },
      { source: "/sanctum/:path*", destination: `${apiUrl}/sanctum/:path*` },
      { source: "/docs/:path*", destination: `${apiUrl}/docs/:path*` },
    ]
  },
  // Reliable hot reload inside Docker bind mounts (Windows/macOS).
  webpack: (config, { dev }) => {
    if (dev) {
      config.watchOptions = {
        poll: 1000,
        aggregateTimeout: 300,
      }
    }
    return config
  },
}

export default nextConfig
