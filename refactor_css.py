import os

css_path = '/home/ronan/Antigravity-x64/Mes projets/GED/css/style_general.css'

with open(css_path, 'r', encoding='utf-8') as f:
    css_content = f.read()

# Replace header styles
header_old = """/* En-tête */
.header {
   background-color: white;
    padding: 1px;
    text-align: center;
   /* color: white;*/
    position: relative;
	width:100%	
}

/* Date et heure */
.header .datetime {
    position: absolute;
    top: 57px;
    left: 76px;
    text-align: center;
    color: white;
}

.header .datetime #date,
.header .datetime #time {
    display: block;
}

/* Logo */
.header .logo {
    position: absolute;
    top: 3px;
    right: 4px;
    width: 110px;
    height: auto;
}"""

header_new = """/* En-tête */
.header {
    background-color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: relative;
    z-index: 10;
}

.header-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.header h1 {
    margin: 0;
    font-size: 1.8rem;
    color: #333;
}

.header p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.95rem;
}

.version-link {
    margin-left: 15px;
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.version-link:hover {
    text-decoration: underline;
}

/* Logo */
.header .logo {
    width: 120px;
    height: auto;
    object-fit: contain;
}"""

navbar_old = """/* Barre de navigation */
.navbar {
    display: flex;
    justify-content: center;
    background-color: #333333a3;
	width: 100%;
}

.navbar a {
    padding: 14px 20px;
    display: block;
    color: white;
    text-align: center;
    text-decoration: none;
}"""

navbar_new = """/* Barre de navigation */
.navbar {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    background-color: rgba(51, 51, 51, 0.9);
    width: 100%;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    gap: 5px;
    padding: 5px;
}

.navbar a {
    padding: 12px 20px;
    display: block;
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease-in-out;
    font-weight: 500;
}"""

form_control_old = """.form-control input,
.form-control select,
.form-control textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

.form-control button {
    background-color: #FF0B0B;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.form-control button:hover {
    background-color: black;
}"""

form_control_new = """.form-control input,
.form-control select,
.form-control textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-sizing: border-box;
    transition: all 0.3s ease;
    font-family: inherit;
    background-color: #fcfcfc;
}

.form-control input:focus,
.form-control select:focus,
.form-control textarea:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    outline: none;
    background-color: #ffffff;
}

.form-control button, button[type="submit"] {
    background-color: #FF0B0B;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px rgba(255, 11, 11, 0.2);
}

.form-control button:hover, button[type="submit"]:hover {
    background-color: #d10000;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(255, 11, 11, 0.3);
}"""

css_content = css_content.replace(header_old, header_new)
css_content = css_content.replace(navbar_old, navbar_new)
css_content = css_content.replace(form_control_old, form_control_new)

# Make .container more modern
container_old = """/* Contenu principal */
.main-content {
    text-align: center;
    padding: 10px;
	background-color: #ffffffad;
}"""

container_new = """/* Contenu principal */
.main-content {
    text-align: center;
    padding: 20px;
    background-color: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    margin-top: 20px;
}"""

css_content = css_content.replace(container_old, container_new)

with open(css_path, 'w', encoding='utf-8') as f:
    f.write(css_content)

print("CSS updated successfully.")
