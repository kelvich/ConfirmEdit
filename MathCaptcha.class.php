<?php

class MathCaptcha extends SimpleCaptcha {

	/** Validate a captcha response */
	function keyMatch( $answer, $info ) {
		return (int)$answer == (int)$info['answer'];
	}

	function addCaptchaAPI( &$resultArr ) {
		list( $sum, $answer ) = $this->pickSum();
		$index = $this->storeCaptcha( array( 'answer' => $answer ) );
		$resultArr['captcha']['type'] = 'math';
		$resultArr['captcha']['mime'] = 'text/tex';
		$resultArr['captcha']['id'] = $index;
		$resultArr['captcha']['question'] = $sum;
	}

	/** Produce a nice little form */
	function getForm() {
		list( $sum, $answer ) = $this->pickSum();
		$index = $this->storeCaptcha( array( 'answer' => $answer ) );

		$form = '<table><tr><td>' . $this->fetchMath( $sum ) . '</td>';
		$form .= '<td>' . Html::input( 'wpCaptchaWord', false, false, array( 'tabindex' => '1', 'required' ) ) . '</td></tr></table>';
		$form .= Html::hidden( 'wpCaptchaId', $index );
		return $form;
	}

	/** Pick a random sum */
	function pickSum() {
    $problems = [
      ['\sum\limits_{n = -\infty}^{\infty} |\psi_n><\psi_n| = ', 1],
      ['e^{-i\pi} = ', -1],
      ['\frac{1}{\alpha_{QED}} = ', 137],
      ['E_{ground} \text{ for Hydrogen (Mev)} = ', -13.6],
      ['\oint_{S} \vec{B} d \vec{S} = ', 0],
      ['\text{S - unitary operator, then  } SS^{+} =', 1],
      ['\frac{e}{\pi i}\int\limits^{\infty}_{-\infty}\frac{e^{it}dt}{t-i} = ', 2]
    ];
    $i = mt_rand( 0, count($problems) - 1);
    $problem = $problems[$i][0];
    $answer = $problems[$i][1];
    return array( $problem, $answer );
	}

	/** Fetch the math */
	function fetchMath( $sum ) {
		if ( MWInit::classExists( 'MathRenderer' ) ) {
			$math = new MathRenderer( $sum );
		} else {
			throw new MWException( 'MathCaptcha requires the Math extension for MediaWiki versions 1.18 and above.' );
		}
		$math->setOutputMode( MW_MATH_PNG );
		$html = $math->render();
		return preg_replace( '/alt=".*?"/', '', $html );
	}
}
