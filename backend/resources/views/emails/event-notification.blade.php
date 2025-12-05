<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">
                                {{ $type === 'started' ? '🎉 Event Started!' : '📢 New Event' }}
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Event Image/Banner -->
                    @if($event->banner_image_url || $event->image_url)
                    <tr>
                        <td style="padding: 0;">
                            <img src="{{ $event->banner_image_url ?? $event->image_url }}" 
                                 alt="{{ $event->name }}" 
                                 style="width: 100%; height: auto; display: block;">
                        </td>
                    </tr>
                    @endif
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="color: #1f2937; margin: 0 0 15px 0; font-size: 24px;">
                                {{ $event->name }}
                            </h2>
                            
                            @if($event->short_description)
                            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                {{ $event->short_description }}
                            </p>
                            @endif
                            
                            @if($event->description)
                            <div style="color: #6b7280; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">
                                {!! nl2br(e($event->description)) !!}
                            </div>
                            @endif
                            
                            <!-- Event Details -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 6px; padding: 15px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 5px 0;">
                                        <strong style="color: #374151;">Start Date:</strong>
                                        <span style="color: #6b7280;">{{ $event->start_date->format('F d, Y h:i A') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;">
                                        <strong style="color: #374151;">End Date:</strong>
                                        <span style="color: #6b7280;">{{ $event->end_date->format('F d, Y h:i A') }}</span>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ $event->url ?: url('/shop?events=' . $event->slug) }}" 
                                           style="display: inline-block; background-color: #7c3aed; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; font-size: 16px;">
                                            {{ $event->button_text ?: 'Shop Now' }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 12px; margin: 0;">
                                You're receiving this email because you're a registered user.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

