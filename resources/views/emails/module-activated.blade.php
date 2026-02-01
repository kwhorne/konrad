<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $module->name }} er aktivert</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 30px; border-radius: 12px 12px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 24px;">
            {{ $company->name }}
        </h1>
    </div>

    <div style="background: #ffffff; padding: 40px 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px;">
        <h2 style="color: #111827; margin-top: 0;">{{ $module->name }} er nå aktivert!</h2>

        <p style="color: #4b5563; font-size: 16px;">
            Hei {{ $user->name }},
        </p>

        <p style="color: #4b5563; font-size: 16px;">
            Vi bekrefter at <strong>{{ $module->name }}</strong> nå er aktivert for <strong>{{ $company->name }}</strong>.
        </p>

        <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 20px; margin: 24px 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #22c55e; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                    <span style="color: white; font-size: 18px;">&#10003;</span>
                </div>
                <div>
                    <p style="color: #166534; font-weight: 600; margin: 0;">{{ $module->name }}</p>
                    @if($module->description)
                        <p style="color: #4ade80; font-size: 14px; margin: 4px 0 0 0;">{{ $module->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <p style="color: #4b5563; font-size: 16px;">
            Du kan nå bruke alle funksjonene i {{ $module->name }}-modulen. Gå til applikasjonen for å komme i gang.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/') }}" style="display: inline-block; background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px;">
                Gå til {{ config('app.name') }}
            </a>
        </div>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

        <p style="color: #9ca3af; font-size: 12px; text-align: center; margin: 0;">
            Denne e-posten ble sendt til {{ $user->email }} fordi du er registrert bruker hos {{ $company->name }}.
        </p>
    </div>

    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </div>
</body>
</html>
