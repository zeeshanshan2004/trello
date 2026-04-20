<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Waiting for Approval - {{ $boardName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-[#1d2125]">
    <div class="flex flex-col items-center justify-center min-h-screen font-sans px-4">
        <div class="text-center max-w-[600px]">
            <!-- Animated Clock Icon -->
            <div class="inline-flex items-center justify-center w-16 h-16 mb-6 rounded-full bg-[#e2b2001a]">
                <svg class="w-8 h-8 text-[#e2b200] animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Heading -->
            <h1 class="text-[24px] font-semibold text-white mb-3">Waiting for admin approval</h1>

            <!-- Dynamic Text -->
            <p class="text-[16px] text-[#9fadbc] leading-relaxed mb-8">
                Your request to join <span class="text-white font-medium">{{ $boardName }}</span> has been sent. You will be able to access the content once an admin approves your request.
            </p>

            <!-- Pending Status Button -->
            <div class="inline-block px-6 py-2.5 bg-[#2c333a] text-[#707d8a] font-medium rounded-[3px] text-sm cursor-not-allowed border border-[#3c444d]">
                Membership Pending
            </div>

            <!-- Footer Link -->
            <div class="mt-8 pt-6 border-t border-[#3c444d]">
                <p class="text-sm text-[#9fadbc]">
                    Logged in as <span class="text-white">{{ auth()->user()->name }}</span>? 
                    <a href="{{ route('dashboard') }}" class="text-[#579dff] hover:underline ml-1">Switch boards</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        const boardId = {{ $board->id }};
        const requestId = {{ $requestId }};
        let checkCount = 0;
        const maxChecks = 120; // Check for 10 minutes (120 * 5 seconds)

        function checkApprovalStatus() {
            fetch(`/boards/${boardId}/join-requests/${requestId}/check-status`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.approved) {
                    // Approved! Redirect to board
                    window.location.href = data.redirect_url;
                } else if (data.rejected) {
                    // Rejected
                    document.querySelector('h1').textContent = 'Request Rejected';
                    document.querySelector('p').innerHTML = 'Your request to join <span class="text-white font-medium">{{ $boardName }}</span> was rejected by an admin.';
                    document.querySelector('.animate-spin').parentElement.innerHTML = '<svg class="w-8 h-8 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                } else {
                    checkCount++;
                    if (checkCount < maxChecks) {
                        setTimeout(checkApprovalStatus, 5000);
                    } else {
                        showTimeoutMessage();
                    }
                }
            })
            .catch(e => {
                console.error('Error checking status:', e);
                checkCount++;
                if (checkCount < maxChecks) {
                    setTimeout(checkApprovalStatus, 5000);
                }
            });
        }

        function showTimeoutMessage() {
            const heading = document.querySelector('h1');
            const text = document.querySelector('p');
            const button = document.querySelector('[class*="bg-\\[#2c333a\\]"]');
            
            heading.textContent = 'Request timeout';
            text.innerHTML = 'Your approval request has timed out. Please contact an admin or try again later.';
            button.textContent = 'Go to Dashboard';
            button.onclick = () => window.location.href = '{{ route("dashboard") }}';
            button.classList.remove('cursor-not-allowed');
            button.classList.add('cursor-pointer', 'hover:bg-[#38414a]');
        }

        // Start checking immediately
        checkApprovalStatus();
    </script>
</body>
</html>
