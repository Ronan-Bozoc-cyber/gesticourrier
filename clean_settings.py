import os
import re

css_path = '/home/ronan/Antigravity-x64/Mes projets/GED/css/settings.css'

with open(css_path, 'r', encoding='utf-8') as f:
    css_content = f.read()

# We want to remove general input, button, table styling from settings.css
# because style_general.css is now the single source of truth for modern UI components.

# We'll just keep the specific styles like .search-container, .expediteur-list, modal, close
# Actually, the modal styles in settings.css are also conflicting if there are modals elsewhere.
# Let's keep modal styles since they might be specific to these 3 pages.
# But let's remove input, select, button generic selectors.

patterns_to_remove = [
    r'input\[type="text"\],\s*input\[type="date"\],\s*select,\s*button\s*\{[^}]*\}',
    r'button\s*\{[^}]*\}',
    r'button:hover\s*\{[^}]*\}',
]

for pattern in patterns_to_remove:
    css_content = re.sub(pattern, '', css_content, flags=re.DOTALL)

with open(css_path, 'w', encoding='utf-8') as f:
    f.write(css_content)

print("settings.css cleaned up.")
