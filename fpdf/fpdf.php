<?php
// Fallback ligero para Reportes: se usa en este proyecto cuando no está instalado FPDF.
// Si instalan la librería oficial, reemplazar este archivo por el oficial.
class FPDF {
    protected $lines = [];
    protected $meta = [];

    public function __construct($orientation='P', $unit='mm', $size='A4') {
        $this->meta = [
            'title' => '',
            'author' => 'Den Den Box',
            'orientation' => $orientation,
            'unit' => $unit,
            'size' => $size
        ];
    }

    public function AddPage() {
        $this->lines[] = "=============== NUEVA PAGINA ===============";
    }

    public function SetFont($family, $style='', $size=0) {
        // no-op en fallback
    }

    public function Cell($w, $h, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        $this->lines[] = trim($txt);
        if ($ln > 0) {
            $this->lines[] = "";
        }
    }

    public function Ln($h=0) {
        $this->lines[] = "";
    }

    public function Output($dest='I', $name='doc.pdf') {
        // Fallback: entrega CSV/Texto en lugar de PDF
        if ($dest === 'D' || $dest==='I') {
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . basename($name) . '"');
            echo implode("\n", $this->lines);
            return;
        }
        echo implode("\n", $this->lines);
    }
}
