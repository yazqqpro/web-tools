# Text to Speech Converter - Security & Management System

## 🔒 Security Features

### 1. CAPTCHA Verification System
- **2-digit math problems** (addition/subtraction)
- **Session-based verification** (30-minute validity)
- **Anti-bot protection** with automatic refresh
- **User-friendly interface** with clear feedback

### 2. Access Control
- **Domain restriction** to `web.app.andrias.web.id`
- **Referer validation** for additional security
- **Origin checking** to prevent unauthorized access
- **Optional IP whitelist** for trusted sources

### 3. Rate Limiting
- **15 requests per hour** per IP address
- **Reduced from 30** due to CAPTCHA protection
- **Automatic cleanup** of rate limit logs

## 🗂️ File Management

### Automatic Cleanup System
- **10-minute file retention** (configurable)
- **Cron job support** for automated cleanup
- **Manual cleanup option** via web interface
- **Comprehensive logging** of all operations

### File Structure
```
tools/text-to-speech-converter/
├── index.php              # Main interface with CAPTCHA
├── tts_proxy.php          # Secure API proxy
├── captcha.php            # CAPTCHA generation & verification
├── cleanup_audio.php      # Automatic file cleanup
├── setup_cron.sh         # Cron job setup script
├── audio/                # Generated audio files
├── logs/                 # Request & cleanup logs
├── rate_limit_logs/      # Rate limiting data
└── README.md            # This documentation
```

## 🚀 Setup Instructions

### 1. Automatic Cleanup (Recommended)
```bash
# Navigate to the TTS directory
cd tools/text-to-speech-converter/

# Run the setup script
./setup_cron.sh
```

This will add a cron job that runs every 10 minutes to clean up old audio files.

### 2. Manual Cleanup
You can also trigger cleanup manually:
```bash
# Command line cleanup
php cleanup_audio.php

# Web-based cleanup (requires daily auth token)
curl "https://yourdomain.com/tools/text-to-speech-converter/cleanup_audio.php?auth=DAILY_TOKEN"
```

### 3. Configuration Options

#### File Retention Period
Edit `cleanup_audio.php` and `tts_proxy.php`:
```php
define('CLEANUP_AGE_MINUTES', 10); // Change to desired minutes
```

#### Rate Limiting
Edit `tts_proxy.php`:
```php
define('RATE_LIMIT_COUNT', 15);    // Requests per hour
define('RATE_LIMIT_WINDOW_SECONDS', 3600); // Time window
```

#### Domain Access
Edit `tts_proxy.php`:
```php
define('ALLOWED_DOMAIN', 'your-domain.com');
```

## 📊 Monitoring & Logs

### Request Logs
Location: `logs/tts_requests.log`
- All TTS requests and responses
- Security events and access attempts
- Error tracking and debugging info

### Cleanup Logs  
Location: `logs/cleanup.log`
- File deletion operations
- Storage space freed
- Cleanup statistics

### Log Format
```json
{
  "timestamp": "2025-01-XX XX:XX:XX",
  "ip": "xxx.xxx.xxx.xxx",
  "user_agent": "Browser/Version",
  "message": "SUCCESS|ERROR|ACCESS_DENIED",
  "context": {...}
}
```

## 🛡️ Security Best Practices

### 1. Regular Monitoring
- Check logs daily for suspicious activity
- Monitor disk usage and cleanup effectiveness
- Review rate limiting effectiveness

### 2. Access Control
- Keep domain restrictions updated
- Consider IP whitelisting for high-security environments
- Monitor referer validation logs

### 3. File Management
- Ensure cron job is running properly
- Monitor audio directory size
- Set up disk space alerts

## 🔧 Troubleshooting

### CAPTCHA Issues
- Check session configuration in PHP
- Verify write permissions for session storage
- Clear browser cache and cookies

### File Cleanup Issues
- Verify cron job is active: `crontab -l`
- Check file permissions on audio directory
- Review cleanup logs for errors

### Access Denied Errors
- Verify domain configuration
- Check referer headers in browser
- Review access logs for details

## 📈 Performance Optimization

### 1. Cleanup Frequency
- Adjust cleanup interval based on usage
- Monitor storage usage patterns
- Consider peak usage times

### 2. Rate Limiting
- Adjust limits based on server capacity
- Monitor for legitimate users being blocked
- Consider different limits for different user types

### 3. Logging
- Rotate logs regularly to prevent disk space issues
- Consider log compression for long-term storage
- Set up log monitoring alerts

## 🔄 Maintenance Tasks

### Daily
- [ ] Check cleanup logs for errors
- [ ] Monitor disk usage
- [ ] Review security logs

### Weekly  
- [ ] Analyze usage patterns
- [ ] Review rate limiting effectiveness
- [ ] Check cron job status

### Monthly
- [ ] Rotate and archive old logs
- [ ] Review and update security settings
- [ ] Performance optimization review

---

**Note**: This system provides robust security and resource management for the TTS converter while maintaining a smooth user experience. Regular monitoring and maintenance ensure optimal performance and security.