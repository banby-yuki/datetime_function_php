<?php


namespace MyApp;

class Calendar {
  public $prev;
  public $next;
  public $yearMonth;
  private $_thisMonth;

  public function __construct() {
    /*URLの値　’t’（日にちが入ってる）から値を取得する。*/
    try {
      /*!isset($_GET['t']は、't'に値がはい入っているかチェックし、! がついているので、
      't'に値が入っていなければExceptionを投げます。という命令文。*/
      /*!preg_match('/\A\d{4}-d{2}\/', $_GET['t'])の preg_match は値の形式がをチェックする。
      今回は、第二引数の値('t')が、d(10進数の数字){4桁}とd(10進数の数字){2桁}の形式になっているかチェック。
      先頭に!がついているので、設定した形式でない場合、Exceptionを投げます。という命令文。*/
      if (!isset($_GET['t']) || !preg_match('/\A\d{4}-\d{2}\z/', $_GET['t'])) {
        throw new \Exception();
      }
      $this->_thisMonth = new \DateTime($_GET['t']);
    } catch (\Exception $e) {
      /*Exceptionが投げられた場合、今月の1日に設定します。という意味。*/
      $this->_thisMonth = new \DateTime('first day of this month');
    }
    $this->prev = $this->_createPrevLink();
    $this->next = $this->_createNextLink();
    /*$this->yearMonthには、$thisMonthに代入されている 2018-05 をformat('F Y')を使い、
     F(月をフルスペルに変換)、Y(4桁の数字に変換)して、$this->yearMonth に代入。*/
    $this->yearMonth = $this->_thisMonth->format('F Y');
  }

  private function _createPrevLink() {
    /*clone(コピー) _thisMonthをなぜするのかというと、基準の値が変わってしまうのを防ぐため、
    _thisMonthのコピーを作成して、コピーの中身を変化させる。*/
    $dt = clone $this->_thisMonth;
    return $dt->modify('-1 month')->format('Y-m');
  }

  private function _createNextLink() {
    /*clone(コピー) $this->_thisMonthをなぜするのかというと、基準の値が変わってしまうのを防ぐため、
    $this->_thisMonthのコピーを作成して、コピーの中身を変化させる。*/
    $dt = clone $this->_thisMonth;
    return $dt->modify('+1 month')->format('Y-m');
  }


  public function show() {
    $tail = $this->_getTail();
    $body = $this->_getBody();
    $head = $this->_getHead();
    $html = '<tr>' . $tail . $body . $head . '</tr>';
    echo $html;
  }

  private function _getTail() {
    $tail = '';
    /* code: 'first day of previous month'を'first day of' . $this->yearMonth . '-1month'　に置き換え。*/
    $lastDayOfPrevMonth = new \DateTime('last day of ' . $this->yearMonth . ' -1month');
    while ($lastDayOfPrevMonth->format('w') < 6) {
      $tail = sprintf('<td class="gray">%d</td>', $lastDayOfPrevMonth->format('d')) . $tail;
      /*subの引数はnew DateInterval('P1D')を使い、
       P1D と書いて31日(31日、30日、29日、、、)ずつ引いていく。*/
      $lastDayOfPrevMonth->sub(new \DateInterval('P1D'));
    }
    return $tail;
  }

  private function _getBody() {
    /*数値（日付）を入れる変数を用意*/
    $body = '';
    /*---いつから、いつまでの日数を生成するか。何日おきに生成するか。範囲指定。*/
    $period = new \DatePeriod(
      /*その月の1日から*/
      /* code: 'first day of this month'を'first day of' . $this->yearMonth　に置き換え。*/
      new \DateTime('first day of ' . $this->yearMonth),
      /*1日おき（１日、2日、３日、4日。。。）*/
      new \DateInterval('P1D'),
      /*翌月の1日からは含みません*/
      /* code: 'first day of next month'を'first day of' . $this->yearMonth . '+1month'　に置き換え。*/
      new \DateTime('first day of ' . $this->yearMonth . ' +1month')
    );
    $today = new \DateTime('today');
    /*foreach(値の数だけループ)を使い、$periodの配列を$dayに代入*/
    foreach ($period as $day) {
      /*$day->format('w')は曜日を0(日)~6(土)で表現することができる。*/
      /*if($day->format('w') === 0){$body .= '</tr><tr>'}は、
       曜日が 0 と等しくなった時 $body に '</tr><tr>' を加えてください、
       ということ。*/
      if ($day->format('w') === '0') { $body .= '</tr><tr>'; }
      $todayClass = ($day->format('Y-m-d') === $today->format('Y-m-d')) ? 'today' : '';
      /* .= は　$body = $body . sprintf(.....)の略
       $body = $body(01) . $body(02) . $body(03).....的な感じ*/
      /*sprintfは（第一引数で指定された形式に第二引数の値を変換する。
       %dは10進数形式に変換ということ。）*/
      /*$day->format('d')の'd'は、
       日付を"01~31"の形式で表すという指定記号*/
      $body .= sprintf('<td class="youbi_%d %s">%d</td>', $day->format('w'), $todayClass, $day->format('d'));
    }
    return $body;
  }

  private function _getHead() {
    $head = '';
    /*DateTimeオブジェクトから翌月の日にちを取得し、
    　$firstDayOfNextMonthをインスタンス化*/
    /* code: 'first day of next month'を'first day of' . $this->yearMonth . '+1month'　に置き換え。*/
    $firstDayOfNextMonth = new \DateTime('first day of' . $this->yearMonth . '+1month');
    /*$firstDayOfNextMonthのメソッドでformat('w')を使い、
     0(日曜)までloopを実行する。*/
    while ($firstDayOfNextMonth->format('w') > 0) {
      /*<td class="gray">今月のカレンダーに表示される翌月はグレーにする。*/
      /*%dには、$firstDayOfNextMonth->format('d')で取得した日にちを代入。*/
      $head .= sprintf('<td class="gray">%d</td>', $firstDayOfNextMonth->format('d'));
      /*$firstDayOfNextMonthのメソッドのaddを使い0(日曜)までの日数を追加していく。*/
      /*addの引数はnew DateInterval('P1D')を使い、
       P1D と書いて1日(1日、2日、3日、、、)ずつ加えていく。*/
      $firstDayOfNextMonth->add(new \DateInterval('P1D'));
    }
    return $head;
  }
}

 ?>
