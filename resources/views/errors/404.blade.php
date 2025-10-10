<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aww.. Don't Cry! | 404</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.5;
        }
        
        .container {
            display: flex;
            align-items: center;
            max-width: 1000px;
            padding: 2rem;
            gap: 0;
        }
        
        .image-section {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
        }
        
        .content-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            min-width: 300px;
            padding-left: 1rem;
        }
        
        .error-image {
            width: 450px;
            height: auto;
            max-width: 100%;
            object-fit: contain;
        }
        
        .error-image-fallback {
            display: none;
            font-size: 10rem;
        }
        
        .main-message {
            font-size: 2.8rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 0.8rem;
            letter-spacing: -0.03em;
            line-height: 1.1;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: none;
        }
        
        .sub-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .sub-message {
            font-size: 1.3rem;
            color: #e74c3c;
            margin-bottom: 1.5rem;
            font-weight: 600;
            line-height: 1.3;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: relative;
        }
        
        .sub-message::after {
            content: '⚡';
            margin-left: 0.5rem;
            font-size: 1.1em;
            animation: pulse 2s infinite;
        }
        
        .description {
            font-size: 1.1rem;
            color: #34495e;
            margin-bottom: 2.5rem;
            line-height: 1.6;
            max-width: 450px;
            font-weight: 500;
            font-style: italic;
            position: relative;
        }
        
        .description::before {
            content: '"';
            font-size: 3rem;
            color: #bdc3c7;
            position: absolute;
            left: -1.5rem;
            top: -0.5rem;
            font-family: Georgia, serif;
        }
        
        .description::after {
            content: '"';
            font-size: 3rem;
            color: #bdc3c7;
            position: absolute;
            right: -1.5rem;
            bottom: -1rem;
            font-family: Georgia, serif;
        }
        
        .btn-container {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #ff7675 0%, #fd79a8 100%);
            box-shadow: 0 4px 15px rgba(255, 118, 117, 0.4);
        }
        
        .btn-secondary:hover {
            box-shadow: 0 8px 25px rgba(255, 118, 117, 0.6);
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .content-section {
                text-align: center;
                min-width: auto;
                justify-content: flex-start;
                padding-left: 0;
            }
            
            .main-message {
                text-align: center;
            }
            
            .sub-content {
                max-width: 500px;
            }
            
            .main-message {
                font-size: 2.2rem;
            }
            
            .sub-message {
                font-size: 1.1rem;
            }
            
            .description {
                font-size: 1rem;
                max-width: 400px;
            }
            
            .description::before,
            .description::after {
                display: none;
            }
            
            .error-image {
                width: 350px;
            }
            
            .btn-container {
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .content-section {
                min-width: auto;
                justify-content: flex-start;
                padding-left: 0;
            }
            
            .main-message {
                text-align: center;
            }
            
            .sub-content {
                max-width: 350px;
            }
            
            .main-message {
                font-size: 1.8rem;
            }
            
            .sub-message {
                font-size: 1rem;
            }
            
            .description {
                max-width: 300px;
            }
            
            .description::before,
            .description::after {
                display: none;
            }
            
            .error-image {
                width: 280px;
            }
            
            .btn-container {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-section">
            <img src="{{ asset('assets/resources/404.png') }}" alt="404 Error" class="error-image" onerror="this.style.display='none'; document.querySelector('.error-image-fallback').style.display='block';">
            <div class="error-image-fallback">😭</div>
        </div>
        
        <div class="content-section">
            <h1 class="main-message">AWWW...DON'T CRY.</h1>
            
            <div class="sub-content">
                <p class="sub-message">It's just a 404 Error!</p>
                
                <p class="description">
                    What you're looking for may have been misplaced in Long Term Memory.
                </p>
                
                <div class="btn-container">
                    <a href="{{ url('/') }}" class="btn">Go Home</a>
                    <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Handle image loading with space in filename
        document.addEventListener('DOMContentLoaded', function() {
            const img = document.querySelector('.error-image');
            const fallback = document.querySelector('.error-image-fallback');
            
            // Try different approaches for the image path
            const imagePaths = [
                "{{ asset('assets/resources/404.png') }}",
                "/assets/resources/404.png"
            ];
            
            let currentPath = 0;
            
            function tryNextPath() {
                if (currentPath < imagePaths.length) {
                    img.src = imagePaths[currentPath];
                    currentPath++;
                } else {
                    // If all paths fail, show fallback
                    img.style.display = 'none';
                    fallback.style.display = 'block';
                }
            }
            
            img.onerror = function() {
                tryNextPath();
            };
            
            // Check if image loads successfully
            img.onload = function() {
                fallback.style.display = 'none';
                img.style.display = 'block';
            };
        });
    </script>
</body>
</html>