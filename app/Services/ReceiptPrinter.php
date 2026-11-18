<?php

namespace App\Services;

use App\Models\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use RuntimeException;

class ReceiptPrinter
{
    public function print(Order $order): string
    {
        $order->loadMissing('items');
        $driver = config('receipt.driver');

        if (! in_array($driver, ['pdf', 'windows', 'file', 'cups'], true)) {
            throw new InvalidArgumentException('RECEIPT_DRIVER harus bernilai pdf, windows, file, atau cups.');
        }

        $pdfPath = 'receipts/'.$order->order_id.'.pdf';
        $options = new Options;
        $options->setIsRemoteEnabled(false);

        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('receipts.pdf', ['order' => $order])->render(), 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();

        if (! Storage::disk('local')->put($pdfPath, $pdf->output())) {
            throw new RuntimeException('Struk PDF gagal disimpan.');
        }

        if ($driver === 'windows') {
            $this->printEscPos(
                $order,
                new WindowsPrintConnector((string) config('receipt.printer')),
            );
        } elseif ($driver === 'file') {
            $spoolPath = storage_path('app/private/receipt-spool/'.$order->order_id.'.bin');
            File::ensureDirectoryExists(dirname($spoolPath));
            $this->printEscPos($order, new FilePrintConnector($spoolPath));
        } elseif ($driver === 'cups') {
            $this->printEscPos(
                $order,
                new CupsPrintConnector((string) config('receipt.printer')),
            );
        }

        return $pdfPath;
    }

    private function printEscPos(
        Order $order,
        CupsPrintConnector|FilePrintConnector|WindowsPrintConnector $connector,
    ): void {
        $printer = new Printer($connector);

        try {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("KOPI KITA\n");
            $printer->setEmphasis(false);
            $printer->text("Struk Transaksi\n");
            $printer->text($order->created_at->format('d/m/Y H:i')."\n");
            $printer->text('No. '.$order->order_id."\n");
            $printer->text('Pembayaran: '.($order->payment_method === 'cash' ? 'Tunai' : $order->payment_method)."\n");
            $printer->text(str_repeat('-', 32)."\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            foreach ($order->items as $item) {
                $printer->text($item->product_name."\n");
                $printer->text($item->quantity.' x Rp'.number_format($item->unit_price, 0, ',', '.')
                    .' = Rp'.number_format($item->line_total, 0, ',', '.')."\n");
            }

            $printer->text(str_repeat('-', 32)."\n");

            if ($order->discount > 0) {
                $printer->text('Diskon: Rp'.number_format($order->discount, 0, ',', '.')."\n");
            }

            $printer->setEmphasis(true);
            $printer->text('TOTAL: Rp'.number_format($order->total, 0, ',', '.')."\n");
            $printer->setEmphasis(false);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Terima kasih!\n");
            $printer->feed(2);
            $printer->cut();
        } finally {
            $printer->close();
        }
    }
}
