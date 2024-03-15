from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

driver = webdriver.Firefox()

driver.get('https://kleinanzeigen.de')

button = WebDriverWait(driver, 10).until(
    EC.element_to_be_clickable((By.ID, 'gdpr-banner-cmp-button'))
)
button.click()

hyperlink = driver.find_element(By.XPATH, '//a[@class="button j-overlay-login"]')
hyperlink.click()

print(driver.title)
