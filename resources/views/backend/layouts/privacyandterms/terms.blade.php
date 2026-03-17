<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $page->title ?? 'Terms & Condition' }}</title>

    <style>
        body {
            background-color: #05402e;
            color: #ffffff;
            font-family: Georgia, serif;
            line-height: 1.6;
            margin: 0;
            padding: 40px 20px;
        }

        main {
            max-width: 1000px;
            margin: auto;
            background-color: #ffffff;
            color: black;
            padding: 40px;
            border-radius: 8px;
        }

        h1,
        h2,
        h3 {
            color: black;
            font-weight: 700;
        }

        h1 {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: .5rem;
        }

        h2 {
            font-size: 1.4rem;
            margin-top: 2rem;
            border-bottom: 1px solid #05402e;
            padding-bottom: .2rem;
            text-transform: uppercase;
        }

        a {
            color: black;
            font-weight: 600;
            text-decoration: underline;
        }

        hr {
            border: none;
            border-bottom: 1px solid #05402e;
            margin: 1.5rem 0;
            opacity: .3;
        }

        /* No Data Card */

        .no-data {
            text-align: center;
            padding: 60px 20px;
            border: 1px dashed #05402e;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .no-data h3 {
            margin-bottom: 10px;
            color: #05402e;
        }

        .no-data p {
            color: #666;
            font-size: 14px;
        }
    </style>

</head>

<body>

    <main>

        @if (!empty($page) && !empty($page->description))
            <h1>{{ $page->title ?? 'Privacy Policy' }}</h1>

            {!! $page->description !!}
        @else
            <div class="no-data">

                <h3>No Data Found</h3>

                <p>
                    The content for this page is currently unavailable.
                    Please check back later.
                </p>

            </div>
        @endif

    </main>

</body>

</html>
