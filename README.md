Copyright (C) 2012 by Kris

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

About
	Bitcoin payment via walletbit.com for WordPress.

Version 0.1
	Currency convert between all currencies automatically.
	
System Requirements:
	WalletBit.com account
	WordPress. Tested against version 3.4.2
	Wordpress E-Commerce plugin from getshopped.org. Tested against version 3.8.9.2
	PHP 5+
  
Configuration Instructions:
	1. Install the Wordpress E-Commerce plugin from getshopped.org
	2. Upload files to your WordPress installation.
	3. Go to your WordPress admin panel. Settings -> Store then Payments. Mark the Bitcoins payment option and click Save Changes below.
	4. In WalletBit.com IPN https://walletbit.com/businesstools/IPN Enter this link http://YOUR_WORDPRESS_URL/?walletbit_callback=true in IPN URL
	5. Enter a strong Security Word in WalletBit IPN.
	6. In module edit "E-Mail" <- set your WalletBit.com email.
	7. In module edit "Token" <- copy from WalletBit.com https://walletbit.com/businesstools/IPN "Token"
	8. In module edit "Security Word" <- Enter the Security Word you created in step 5.
	9. Click Update ».