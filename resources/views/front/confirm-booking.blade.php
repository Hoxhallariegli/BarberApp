<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmim Rezervimi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
        <h1 class="text-2xl font-bold mb-4">Përshëndetje, {{ $booking->customer_name ?: 'Klient' }}</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                {{ session('success') }}
            </div>
        @elseif(session('info'))
            <div class="bg-blue-100 text-blue-700 p-4 rounded mb-4">
                {{ session('info') }}
            </div>
        @else
            <p class="text-gray-600 mb-6">
                Ju keni një rezervim sot në orën <span class="font-bold text-black">{{ $booking->appointment_datetime->format('H:i') }}</span>.
                A mund ta konfirmoni pjesëmarrjen tuaj?
            </p>

            <div class="flex flex-col space-y-3">
                <form action="{{ url('/confirm/'.$booking->token.'/yes') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition">
                        Po, do të vij
                    </button>
                </form>

                <form action="{{ url('/confirm/'.$booking->token.'/no') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-lg transition">
                        Jo, nuk mundem
                    </button>
                </form>
            </div>
        @endif

        <div class="mt-8 pt-6 border-t border-gray-100">
            <p class="text-sm text-gray-500">Faleminderit që zgjodhët sallonin tonë!</p>
        </div>
    </div>
</body>
</html>
