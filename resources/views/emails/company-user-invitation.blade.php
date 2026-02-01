<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitasjon til {{ $company->name }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 30px; border-radius: 12px 12px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 24px;">
            {{ $company->name }}
        </h1>
    </div>

    <div style="background: #ffffff; padding: 40px 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px;">
        <h2 style="color: #111827; margin-top: 0;">Hei {{ $user->name }},</h2>

        <p style="color: #4b5563; font-size: 16px;">
            @if($invitedBy)
                {{ $invitedBy->name }} har invitert deg til å bli med i <strong>{{ $company->name }}</strong> som {{ $roleName }}.
            @else
                Du har blitt invitert til å bli med i <strong>{{ $company->name }}</strong> som {{ $roleName }}.
            @endif
        </p>

        @if($company->address || $company->city)
        <div style="background: #f9fafb; border-radius: 8px; padding: 16px; margin: 20px 0;">
            <p style="color: #6b7280; font-size: 14px; margin: 0;">
                <strong>Om selskapet:</strong><br>
                {{ $company->name }}<br>
                @if($company->organization_number)
                    Org.nr: {{ $company->formatted_organization_number }}<br>
                @endif
                @if($company->address)
                    {{ $company->address }}<br>
                @endif
                @if($company->postal_code && $company->city)
                    {{ $company->postal_code }} {{ $company->city }}
                @endif
            </p>
        </div>
        @endif

        <p style="color: #4b5563; font-size: 16px;">
            Klikk på knappen nedenfor for å sette opp din konto og velge et passord:
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $acceptUrl }}" style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 16px;">
                Aksepter invitasjon
            </a>
        </div>

        <p style="color: #6b7280; font-size: 14px;">
            Hvis knappen ikke fungerer, kopier og lim inn denne lenken i nettleseren din:
        </p>

        <p style="color: #4f46e5; font-size: 14px; word-break: break-all;">
            {{ $acceptUrl }}
        </p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

        <p style="color: #9ca3af; font-size: 12px; text-align: center; margin: 0;">
            Denne invitasjonen ble sendt til {{ $user->email }}.<br>
            Hvis du ikke forventet denne e-posten, kan du trygt ignorere den.
        </p>
    </div>

    <div style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
        &copy; {{ date('Y') }} {{ $company->name }}
    </div>
</body>
</html>
