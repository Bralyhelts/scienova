<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

$answer = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $subject = trim($_POST['subject'] ?? '');

    $level = $_POST['class_level'] ?? 'S5';

    $kind = $_POST['kind'] ?? 'scenario';

    $topic = trim($_POST['topic'] ?? '');

    if (
        !in_array($level, ['S5', 'S6'], true) ||
        !in_array(
            $kind,
            ['scenario', 'questions', 'marking'],
            true
        ) ||
        $subject === '' ||
        $topic === ''
    ) {

        $error = 'Complete all the required fields.';

    } elseif (empty(cfg('ai')['api_key'])) {

        $error = 'AI API key has not been configured.';

    } else {

        $prompt = "

        You are an experienced Uganda A-Level
        educational assistant.

        Create original learning material based
        on the current competency-based approach.

        Class: $level

        Subject: $subject

        Topic: $topic

        Output type: $kind

        Provide:

        1. A realistic learning scenario.

        2. Relevant examination questions.

        3. Clear instructions.

        4. Appropriate learning competencies.

        5. Suggested marking guidance where relevant.

        Do not claim that generated questions
        are official past examination papers.

        Advise learners to verify curriculum
        alignment with their teachers and
        official educational materials.

        ";

        $payload = [

            'model' => cfg('ai')['model'],

            'input' => $prompt

        ];

        $ch = curl_init(
            cfg('ai')['endpoint']
        );

        curl_setopt_array($ch, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_HTTPHEADER => [

                'Content-Type: application/json',

                'Authorization: Bearer ' .
                cfg('ai')['api_key']

            ],

            CURLOPT_POSTFIELDS => json_encode($payload),

            CURLOPT_TIMEOUT => 60

        ]);

        $response = curl_exec($ch);

        $status = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        $data = json_decode(
            (string)$response,
            true
        );

        if (
            $status >= 200 &&
            $status < 300
        ) {

            $answer = $data['output_text'] ?? '';

            if (
                !$answer &&
                isset($data['output'])
            ) {

                foreach ($data['output'] as $output) {

                    foreach (
                        ($output['content'] ?? [])
                        as $content
                    ) {

                        if (isset($content['text'])) {

                            $answer .= $content['text'];

                        }

                    }

                }

            }

        }

        if (!$answer) {

            $error = 'AI service did not return a response.';

        }

    }

}

page_header('AI Question Generator');

?>

<h1>ELITESHELF AI LEARNING ASSISTANT</h1>

<p>
    Generate competency-based scenarios,
    examination questions and marking guides.
</p>


<?php if (!empty($error)): ?>

    <div class="notice error">

        <?= e($error) ?>

    </div>

<?php endif; ?>


<form class="panel" method="POST">

    <?= csrf_field() ?>

    <label>Class</label>

    <select name="class_level">

        <option value="S5">Senior Five</option>

        <option value="S6">Senior Six</option>

    </select>


    <label>Subject</label>

    <input
        type="text"
        name="subject"
        placeholder="e.g. Chemistry"
        required
    >


    <label>Topic</label>

    <input
        type="text"
        name="topic"
        placeholder="Enter your topic"
        required
    >


    <label>Type of Material</label>

    <select name="kind">

        <option value="scenario">
            Competency-Based Scenario
        </option>

        <option value="questions">
            Examination Questions
        </option>

        <option value="marking">
            Suggested Marking Guide
        </option>

    </select>


    <button class="btn">
        Generate Material
    </button>

</form>


<?php if ($answer): ?>

    <div class="panel">

        <h2>Generated Learning Material</h2>

        <div class="output">

            <?= nl2br(e($answer)) ?>

        </div>

    </div>

<?php endif; ?>


<p class="muted">
    AI-generated educational material should be
    checked against current official curriculum
    documents and teacher guidance.
</p>


<?php page_footer(); ?>
