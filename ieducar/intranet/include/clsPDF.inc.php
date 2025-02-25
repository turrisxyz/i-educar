<?php

class clsPDF
{
    public $nome;
    public $titulo;
    public $palavrasChaves;
    public $largura;
    public $altura;
    public $numeroPagina;
    public $renderCapa;
    public $pagOpened = false;

    public $caminho;
    public $linkArquivo;
    public $depurar;
    public $pdf;

    public $topmargin;
    public $bottommargirn;
    public $leftmargin;
    public $rigthmargin;

    public $listagem;
    public $detalhe;

    public $owner = 'PMI - Prefeitura Municipal de Itajaí';

    public function __construct(
        $nome,
        $titulo,
        $tamanhoFolha,
        $palavrasChaves,
        $depurar = false,
        $reder = true
    ) {
        $this->nome = $nome;
        $this->titulo = $titulo;
        $this->palavrasChaves = $palavrasChaves;
        $this->depurar = $depurar;
        $this->numeroPagina = -1;
        $this->renderCapa = $reder;
        $this->MakeTamPage($tamanhoFolha);

        $this->OpenFile();
    }

    public function OpenFile()
    {
        $caminho = 'tmp/';
        $lim_dir = opendir('tmp/');
        $fonte   = base_path('ieducar/intranet/arquivos/fontes/FreeMonoBold.ttf');

        while ($lim_file = readdir($lim_dir)) {
            if ($lim_file != '.' && $lim_file != '..') {
                if (! (substr_count($lim_file, date('Y-m-d')))) {
                    @unlink('tmp/' . $lim_file);
                }
            }
        }

        $caminho .= date('Y-m-d') . '-';
        list($usec, $sec) = explode(' ', microtime());
        $caminho .= substr(md5($usec . $sec), 0, 8);
        $caminho .= '.pdf';

        $this->caminho     = base_path('ieducar/intranet/' . $caminho);
        $this->LinkArquivo = $caminho;

        $this->pdf = PDF_new();
        pdf_set_parameter($this->pdf, 'FontOutline', 'monospaced=' . $fonte);
        PDF_open_file($this->pdf, $this->caminho);

        PDF_set_info($this->pdf, 'Creator', $this->owner);
        PDF_set_info($this->pdf, 'Author', $this->owner);
        PDF_set_info($this->pdf, 'Title', $this->titulo);

        if ($this->depurar) {
            echo '<b>PDF:</b> Objeto criado!<br>';
            echo '<b>PDF:</b> O objeto foi criado no seguinte local -> ' . $this->LinkArquivo . '<br>';
        }

        $this->OpenPage();
    }

    public function CloseFile()
    {
        $this->ClosePage();
        PDF_close($this->pdf);
        PDF_delete($this->pdf);

        if ($this->depurar) {
            echo "<b>PDF:</b> Finalizando o arquivo com tamanho de -> {$len}<br>";
        }

        return $len;
    }

    public function GetLink()
    {
        return $this->LinkArquivo;
    }

    public function OpenPage()
    {
        if ($this->numeroPagina > -1) {
            // Construção de página normal
            $this->ClosePage();
            $this->numeroPagina++;
            PDF_begin_page_ext($this->pdf, $this->largura, $this->altura, '');
            $this->pagOpened = true;
        } else {
            $this->numeroPagina++;

            if ($this->renderCapa) {
                $this->MakeCapa();
            }
        }
    }

    public function ClosePage()
    {
        if ($this->pagOpened) {
            PDF_end_page($this->pdf);
            $this->pagOpened = false;
        }

        if ($this->depurar) {
            echo "<b>PDF:</b> Finalizando pagina -> {$this->numeroPagina}<br>";
        }
    }

    public function MakeTamPage($tamanhoFolha)
    {
        $this->largura = 0;
        $this->altura  = 0;

        $this->topmargin     = 50;
        $this->bottommargirn = 50;
        $this->leftmargin  = 40;
        $this->rigthmargin = 60;

        switch ($tamanhoFolha) {
      case 'A0':
        $this->largura  = 2380.0;
        $this->altura   = 3368.0;
        break;
      case 'A1':
        $this->largura  = 1684.0;
        $this->altura   = 2380.0;
        break;
      case 'A2':
        $this->largura  = 1190.0;
        $this->altura   = 1684.0;
        break;
      case 'A3':
        $this->largura  = 842.0;
        $this->altura   = 1190.0;
        break;
      case 'A4':
        $this->largura  = 595.0;
        $this->altura   = 842.0;
        break;
      case 'A4h':
        $this->largura  = 595.0;
        $this->altura   = 842.0;
        break;
      case 'A5':
        $this->largura  = 421.0;
        $this->altura   = 595.0;
        break;
      case 'A6':
        $this->largura  = 297.0;
        $this->altura   = 421.0;
        break;
      case 'B5':
        $this->largura  = 501.0;
        $this->altura   = 709.0;
        break;
      case 'letter':
        $this->largura  = 612.0;
        $this->altura   = 792.0;
        break;
      case 'ledger':
        $this->largura  = 1224.0;
        $this->altura   = 792.0;
        break;
      case 'p11x17':
        $this->largura  = 792.0;
        $this->altura   = 1224.0;
        break;
    }

        if ($this->depurar) {
            echo "<b>PDF:</b> Tamanho da pagina equivalente à -> {$tamanhoFolha}<br>";
        }
    }

    public function getTamPage()
    {
        return [$this->largura, $this->altura];
    }

    public function MakeCapa()
    {
        if ($this->depurar) {
            echo '<b>PDF:</b> Confeccionando capa para relatório. <br>';
        }
    }

    public function SetFill($color='#FFFFFF', $transparency = '0')
    {
        if (strlen($color) != 7 || $color[0] != '#') {
            if ($this->depurar) {
                echo '<b>PDF:</b> Não foi possivel setar o fundo. <br>';
            }

            return false;
        }

        $r = hexdec(substr($color, 1, 2)) / 255;
        $g = hexdec(substr($color, 3, 2)) / 255;
        $b = hexdec(substr($color, 5, 2)) / 255;
        $a = $transparency;

        PDF_setcolor($this->pdf, 'fill', 'rgb', $r, $g, $b, 0);

        if ($this->depurar) {
            echo "<b>PDF:</b> Linha setada na cor -> {$color}. <br>";
        }

        return true;
    }

    public function SetBoth($color = '#000000', $transparency = '0')
    {
        if (strlen($color) != 7 || $color[0] != '#') {
            if ($this->depurar) {
                echo '<b>PDF:</b> Não foi possivel setar a linha. <br>';
            }

            return false;
        }

        $r = hexdec(substr($color, 1, 2)) / 255;
        $g = hexdec(substr($color, 3, 2)) / 255;
        $b = hexdec(substr($color, 5, 2)) / 255;
        $a = $transparency;

        PDF_setcolor($this->pdf, 'both', 'rgb', $r, $g, $b, 0);

        if ($this->depurar) {
            echo "<b>PDF:</b> Fundo setado na cor -> {$color}. <br>";
        }

        return true;
    }

    public function SetLine($largura)
    {
        PDF_setlinewidth($this->pdf, $largura);

        if ($this->depurar) {
            echo "<b>PDF:</b> Linha com largura -> {$largura}. <br>";
        }
    }

    public function SetFont($fonte, $tamanho)
    {
        $f_user = match ($fonte) {
            'normal' => 'Courier',
            'courier' => 'Courier-Bold',
            'courierItalico' => 'Courier-BoldOblique',
            'normalItalico' => 'Helvetica-BoldOblique',
            'times' => 'Times-Bold',
            'timesItalico' => 'Times-BoldItalic',
            'symbol' => 'ZapfDingbats',
            'monospaced' => 'monospaced',
            default => 'Helvetica-Bold',
        };
        $font = PDF_findfont($this->pdf, $f_user, 'host', 0);
        PDF_setfont($this->pdf, $font, $tamanho);

        if ($this->depurar) {
            echo '<b>PDF:</b> Fonte atual de uso: ' . $f_user . '<br>';
        }
    }

    public function InsertJpng($tipo, $image, $x, $y, $tamanho)
    {
        $y = $this->altura - $y;

        $im = pdf_open_image_file($this->pdf, $tipo, $image, '', 0);

        PDF_place_image($this->pdf, $im, $x, $y, $tamanho);
        $x = PDF_get_value($this->pdf, 'imagewidth', $im);
        $y = PDF_get_value($this->pdf, 'imageheight', $im);
        PDF_close_image($this->pdf, $im);
    }

    /**
     * Adiciona uma imagem no documento PDF escalonando-a até a largura desejada.
     *
     * @param string    $tipo     Tipo de imagem a ser incorporada
     * @param string    $image    Caminho para o arquivo da imagem
     * @param int|float $x        Posição x (eixo horizontal)
     * @param int|float $y        Posição y (eixo vertical)
     * @param int|float $maxWidth Largura máxima da imagem (usado para o cálculo de redução proporcional)
     */
    public function insertImageScaled($tipo, $image, $x, $y, $maxWidth)
    {
        if ($image == '') {
            throw new Exception('Parametro $image vazio');
        }

        $image = realpath($image);
        if (! is_readable($image)) {
            throw new Exception('Caminho para arquivo de imagem inválido: "' . $image . '"');
        }

        $y = $this->altura - $y;
        $im = pdf_open_image_file($this->pdf, $tipo, $image, '', 0);

        /**
         * Reduz em dois pixels. Algum bug da função da PDFLib necessita essa
         * compensação no cálculo para redução proporcional.
         */
        $maxWidth -= 2;

        $scale = 1;
        $width = PDF_get_value($this->pdf, 'imagewidth', $im);
        if ($width > $maxWidth) {
            $scale = $maxWidth / $width;
        }

        PDF_place_image($this->pdf, $im, $x, $y, $scale);
        PDF_close_image($this->pdf, $im);
    }

    public function LinkFor($type, $stringlink, $destino, $xo, $yo, $x, $y)
    {
        if ($type == 'web') {
            PDF_add_weblink($this->pdf, $xo, $yo, $x, $y, $stringlink, $destino);
        } elseif ($type == 'file') {
            PDF_add_locallink($this->pdf, $xo, $yo, $x, $y, $stringlink, $destino);
        }
    }

    public function MakeDetalhe()
    {
        return false;
    }

    public function MakeListagem()
    {
        return false;
    }

    public function Shape($tipo, $x, $y, $largura=0, $altura=0, $linha=0.001, $color='#000000', $color2='#FFFFFF')
    {
        $this->SetLine($linha);
        $this->SetBoth($color);
        $this->SetFill($color2);

        switch ($tipo) {
      case 'ret':
        PDF_rect($this->pdf, $x, $y, $largura, $altura);
        break;
      case 'elipse':
        PDF_circle($this->pdf, $x, $y, $largura);
        break;
    }

        PDF_fill_stroke($this->pdf);

        if ($this->depurar) {
            echo '<b>PDF:</b> Adicionado um shape.<br>';
        }
    }

    /**
     * Funcao que desenha um quadrado (de cima para baixo, da esqueda para direita)
     * recebe todas as variaveis de posicao (X,Y) em valores absolutos
     * x,y = 0,0 é o topo esquerdo da pagina
     *
     * @param int    $x_topleft
     * @param int    $y_topleft
     * @param int    $x_bottomright
     * @param int    $y_bottomright
     * @param float  $linha
     * @param string $color
     * @param string $color2
     */
    public function quadrado_absoluto(
        $x_topleft,
        $y_topleft,
        $x_bottomright,
        $y_bottomright,
        $linha = 0.1,
        $color = '#000000',
        $color2 = '#FFFFFF'
    ) {
        $altura  = $y_bottomright - $y_topleft;
        $largura = $x_bottomright - $x_topleft;
        $this->quadrado_relativo(
            $x_topleft,
            $y_topleft,
            $largura,
            $altura,
            $linha,
            $color,
            $color2
        );
    }

    /**
     * Funcao que desenha um quadrado (de cima para baixo, da esqueda para direita)
     * recebe todas as variaveis de posicao (X,Y) para o inicio da caixa
     * recebe ainda os parametros altura e largura, relativos.
     * 0,0 é o topo esquerdo da pagina
     *
     * @param int    $x_topleft
     * @param int    $y_topleft
     * @param int    $largura
     * @param int    $altura
     * @param float  $linha
     * @param string $color
     * @param string $color2
     */
    public function quadrado_relativo(
        $x_topleft,
        $y_topleft,
        $largura,
        $altura,
        $linha = 0.1,
        $color = '#000000',
        $color2 = '#FFFFFF'
    ) {
        $this->Shape(
            'ret',
            $x_topleft,
            $this->altura - $y_topleft - $altura,
            $largura,
            $altura,
            $linha,
            $color,
            $color2
        );
    }

    public function Line(
        $xo,
        $yo,
        $x,
        $y,
        $linha = 2.001,
        $color1 = '#000000',
        $color2 = '#000000',
        $teck = true,
        $teck2 = true
    ) {
        if ($teck2) {
            $this->SetLine($linha);
            $this->SetBoth($color1);
            $this->SetFill($color2);
        }

        PDF_moveto($this->pdf, $xo, $yo);
        PDF_lineto($this->pdf, $x, $y);

        if ($teck) {
            PDF_stroke($this->pdf);
        }

        if ($this->depurar) {
            echo '<b>PDF:</b> Adicionado uma linha.<br>';
        }
    }

    /**
     * Funcao que desenha uma linha (de cima para baixo, da esqueda para direita)
     * recebe todas as variaveis de posicao (X,Y) em valores absolutos
     * x,y = 0,0 é o topo esquerdo da pagina
     *
     * @param int    $x_topleft
     * @param int    $y_topleft
     * @param int    $x_bottomright
     * @param int    $y_bottomright
     * @param float  $linha
     * @param string $color
     * @param string $color2
     */
    public function linha_absoluta(
        $x_topleft,
        $y_topleft,
        $x_bottomright,
        $y_bottomright,
        $linha = 0.1,
        $color = '#000000',
        $color2 = '#FFFFFF'
    ) {
        $this->Line(
            $x_topleft,
            $this->altura - $y_topleft,
            $x_bottomright,
            $this->altura - $y_bottomright,
            $linha,
            $color,
            $color2
        );
    }

    /**
     * Funcao que desenha uma linha (de cima para baixo, da esqueda para direita)
     * recebe todas as variaveis de posicao (X,Y) para o inicio da linha
     * recebe ainda os parametros altura e largura, relativos.
     * 0,0 é o topo esquerdo da pagina
     *
     * @param int    $x_topleft
     * @param int    $y_topleft
     * @param int    $largura
     * @param int    $altura
     * @param float  $linha
     * @param string $color
     * @param string $color2
     */
    public function linha_relativa(
        $x_topleft,
        $y_topleft,
        $largura,
        $altura,
        $linha = 0.1,
        $color = '#000000',
        $color2 = '#FFFFFF'
    ) {
        $this->Line(
            $x_topleft,
            $this->altura - $y_topleft,
            $x_topleft + $largura,
            $this->altura - $y_topleft - $altura,
            $linha,
            $color,
            $color2
        );
    }

    public function Curve(
        $xo,
        $yo,
        $x,
        $y,
        $px1,
        $py1,
        $px2,
        $py2,
        $linha = 2.001,
        $color1 = '#000000',
        $color2 = '#000000'
    ) {
        if ($teck2) {
            $this->SetLine($linha);
            $this->SetBoth($color1);
            $this->SetFill($color2);
        }

        PDF_moveto($this->pdf, $xo, $yo);
        PDF_curveto($this->pdf, $px1, $py1, $px2, $py2, $x, $y);

        if ($teck) {
            PDF_stroke($this->pdf);
        }

        if ($this->depurar) {
            echo '<b>PDF:</b> Adicionado uma curva.<br>';
        }
    }

    public function Write(
        $msg,
        $xo,
        $yo,
        $x,
        $y,
        $fonte = 'normal',
        $tamanho = '10',
        $color = '#888888',
        $align = 'center',
        $local = 'box'
    ) {
        $this->SetFont($fonte, $tamanho);
        $this->SetBoth($color);

        switch ($local) {
      case 'xy':
        PDF_show_xy($this->pdf, $msg, $xo, $yo);
        break;
      default:
        // 'box'
        $yo = $this->altura - $yo;
        PDF_show_boxed($this->pdf, $msg, $xo, $yo, $x, $y, $align, '');
    }

        if ($this->depurar) {
            echo "<b>PDF:</b> Adicionado o texto: <pre>$msg</pre><br>";
        }
    }

    /**
     * Funcao que escreve um texto na pagina (de cima para baixo, da esqueda para direita)
     * recebe as variaveis de posicao (X,Y) para o inicio do texto em valores absolutos
     * recebe ainda os parametros largura e altura, relativos
     * x,y = 0,0 é o topo esquerdo da pagina
     *
     * @param string $texto
     * @param int    $x_topleft
     * @param int    $y_topleft
     * @param int    $largura
     * @param int    $altura
     * @param string $fonte
     * @param int    $tamanho
     * @param string $color
     * @param string $align
     * @param string $local
     */
    public function escreve_relativo(
        $texto,
        $x_topleft,
        $y_topleft,
        $largura,
        $altura,
        $fonte = 'arial',
        $tamanho = '10',
        $color = '#000000',
        $align = 'left'
    ) {
        $this->Write(
            $texto,
            $x_topleft,
            $y_topleft + $altura,
            $largura,
            $altura,
            $fonte,
            $tamanho,
            $color,
            $align
        );
    }

    public function escreve_relativo_center(
        $texto,
        $x_topleft,
        $y_topleft,
        $largura,
        $altura,
        $fonte = 'arial',
        $tamanho = '10',
        $color = '#000000',
        $align = 'center'
    ) {
        $this->escreve_relativo(
            $texto,
            $x_topleft,
            $y_topleft,
            $largura,
            $altura,
            $fonte,
            $tamanho,
            $color,
            $align
        );
    }

    /**
     * Funcao que escreve um texto na pagina (de cima para baixo, da esqueda para direita)
     * recebe todas as variaveis de posicao (X,Y) em valores absolutos
     * x,y = 0,0 é o topo esquerdo da pagina
     *
     * @param string $texto
     * @param int    $x_topleft
     * @param int    $y_topleft
     * @param int    $x_bottomright
     * @param int    $y_bottomright
     * @param string $fonte
     * @param int    $tamanho
     * @param string $color
     * @param string $align
     * @param string $local
     */
    public function escreve_absoluto(
        $texto,
        $x_topleft,
        $y_topleft,
        $x_bottomright,
        $y_bottomright,
        $fonte = 'arial',
        $tamanho = '10',
        $color = '#000000',
        $align = 'left'
    ) {
        $this->Write(
            $texto,
            $x_topleft,
            $y_bottomright,
            $x_bottomright - $x_topleft,
            $y_bottomright - $y_topleft,
            $fonte,
            $tamanho,
            $color,
            $align
        );
    }

    public function PagAgenda($texto, $dia_semana_v, $data_atual_v, $lembrete)
    {
        $this->OpenPage();
        $this->Shape('ret', 30, 30, $this->largura-60, $this->altura-60, 2);
        $this->InsertJpng('jpeg', 'imagens/brasao.jpg', 40, 95, 0.1);

        $msg = $this->titulo;
        $this->Write($msg, 40, 142, 300, 40, 'courier', 10, '#333333', 'left');

        $lembrete = "Lembrete:\r\n".$lembrete;

        $this->Write($lembrete, 160, 150, 400, 115, 'courier', 8, '#333333', 'left');
        $this->printData($this->altura-140, $dia_semana_v, $data_atual_v, 140);

        $this->Write(
            $texto,
            40,
            $this->altura - 30,
            $this->largura - 80,
            $this->altura - 180,
            'courier',
            10,
            '#333333',
            'left'
        );

        $this->ClosePage();
    }

    public function printData($al, $dia_semana_v, $data_atual_v, $l)
    {
        $this->Shape('ret', 30, $al, $this->largura-60, 15, 2, '#000000', '#AAAAAA');
        $msg = "Dia: {$dia_semana_v} ({$data_atual_v})";
        $this->Write($msg, 34, $l, $this->largura-62, 15, 'courier', 10, '#333333', 'left');
    }
}
