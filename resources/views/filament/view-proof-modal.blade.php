<div class="space-y-4 text-sm">
    <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <div>
            <span class="text-xs text-gray-500 uppercase block">Invoice ID</span>
            <span class="font-bold">#{{ $transaction->invoice_id }}</span>
        </div>
        <div>
            <span class="text-xs text-gray-500 uppercase block">Payable Amount</span>
            <span class="font-bold text-emerald-600">{{ $transaction->currency }} {{ number_format((float) $transaction->amount, 2) }}</span>
        </div>
        <div>
            <span class="text-xs text-gray-500 uppercase block">Customer TRX / UTR</span>
            <span class="font-mono font-bold">{{ $transaction->transaction_id }}</span>
        </div>
        <div>
            <span class="text-xs text-gray-500 uppercase block">Sender Account Name</span>
            <span class="font-medium">{{ $transaction->sender_name }}</span>
        </div>
        @if($transaction->bank_account_name)
        <div class="col-span-2">
            <span class="text-xs text-gray-500 uppercase block">Paid To Account</span>
            <span class="font-medium">{{ $transaction->bank_account_name }}</span>
        </div>
        @endif
        @if($transaction->customer_notes)
        <div class="col-span-2">
            <span class="text-xs text-gray-500 uppercase block">Customer Note</span>
            <p class="text-gray-700 dark:text-gray-300 italic">{{ $transaction->customer_notes }}</p>
        </div>
        @endif
    </div>

    @if($transaction->proof_image)
        @php
            $proofUrl = str_starts_with($transaction->proof_image, 'http')
                ? $transaction->proof_image
                : asset('storage/' . $transaction->proof_image);
            $isPdf = str_ends_with(strtolower($transaction->proof_image), '.pdf');
        @endphp

        <div class="border rounded-xl overflow-hidden bg-black/5 dark:bg-white/5 p-2">
            @if($isPdf)
                <div class="p-6 text-center">
                    <p class="text-gray-600 dark:text-gray-400 mb-3">Customer uploaded a PDF receipt.</p>
                    <a href="{{ $proofUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                        <span>Open PDF Receipt</span>
                    </a>
                </div>
            @else
                <div class="text-center">
                    <img src="{{ $proofUrl }}" alt="Payment Proof" class="max-h-[500px] mx-auto object-contain rounded-lg shadow-sm">
                    <div class="mt-2">
                        <a href="{{ $proofUrl }}" target="_blank" class="text-xs text-primary-600 hover:underline">
                            Open full size image in new tab &rarr;
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="p-4 text-center text-gray-500 italic bg-gray-50 rounded-lg">
            No screenshot attached. Transaction reference only.
        </div>
    @endif
</div>
