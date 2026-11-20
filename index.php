<?php

require __DIR__ . '/config/bootstrap.php';

page_header('Home');

?>

<section class="hero">

    <div>

        <span class="eyebrow">
            A-LEVEL DIGITAL LIBRARY
        </span>

        <h1>ELITESHELF</h1>

        <h2>
            Unlock Knowledge.
            Empower Excellence.
            Shape the Future.
        </h2>

        <p>
            Your trusted digital learning library
            for Arts and Science students.
            Access past papers, practise examination
            questions and improve your academic performance.
        </p>

        <a class="btn" href="library.php">
            Explore Library
        </a>

        <a class="btn secondary" href="ai.php">
            AI Question Generator
        </a>

    </div>

</section>


<section class="section">

    <h2>Explore Our Libraries</h2>

    <div class="cards">

        <a class="tile" href="library.php?stream=Arts">

            <h3>ARTS LIBRARY</h3>

            <p>
                History, Geography, Economics,
                Literature and other Arts subjects.
            </p>

        </a>


        <a class="tile" href="library.php?stream=Science">

            <h3>SCIENCE LIBRARY</h3>

            <p>
                Biology, Chemistry, Physics,
                Mathematics and other Science subjects.
            </p>

        </a>

    </div>

</section>


<section class="section">

    <h2>Choose Your Class</h2>

    <div class="cards">

        <a class="tile" href="library.php?class=S5">

            <h3>SENIOR FIVE</h3>

            <p>Access S5 past examination papers.</p>

        </a>


        <a class="tile" href="library.php?class=S6">

            <h3>SENIOR SIX</h3>

            <p>Access S6 past examination papers.</p>

        </a>

    </div>

</section>


<section class="section">

    <h2>Learn Smarter with ELITESHELF AI</h2>

    <p>
        Generate competency-based scenarios,
        examination questions and marking guides
        according to your learning topics.
    </p>

    <a href="ai.php" class="btn">
        Start Learning
    </a>

</section>


<?php

page_footer();

?>
