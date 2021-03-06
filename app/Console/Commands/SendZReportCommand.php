<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\TraInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendZReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zreport:send {transaction? : The ID of the transaction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Z Report Every day at 23:50';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $transactionID = $this->argument('transaction');

        if ($transactionID != null) {
            $this->info('fetching the transaction');
            $transaction = Transaction::whereId($transactionID)->first();
            if ($transaction != null) {
                //post receipt
                if ($transaction->post_receipt_status == 0) {
                    //check if the receipt was posted already
                    $receiptResponse = (new TraInvoiceService())->postZReport($transaction);
                    $responseCode = $receiptResponse['code'];
                    Log::info($receiptResponse['code']);
                    Log::info($receiptResponse['message']);
                    if ($responseCode = 200) {
                        $transaction->post_receipt_status = 1;
                        $transaction->update();
                    } else {
                        Log::info($receiptResponse['code']);
                        Log::info($receiptResponse['message']);
                        $this->alert('Receipt was already posted for this transaction');
                    }
                } else {
                    $this->alert('Transaction with this ID was not found in the database, please try again');

                }
            }

            $this->alert('Done');

        }
    }
}
