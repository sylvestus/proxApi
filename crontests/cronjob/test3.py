import re
import subprocess
import os
import subprocess
import time
import logging
from urllib.parse import urlparse



#configuring  email
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart


def sendEmail(subject,body):
        # Set up email parameters
        sender_email = 'silvanussigei1996@gmail.com'
        # receiver_email = 'silvanussigei19960@gmail.com'
        receiver_emails = ['horine12@gmail.com', 'sylvestus.sigei@student.moringaschool.com']


        password = 'aikknlyiqsrfqffz'


        # Create message object
        message = MIMEMultipart()
        message['From'] = sender_email
        message['To'] = ', '.join(receiver_emails)

        message['Subject'] = subject
        message.attach(MIMEText(body, 'plain'))

        # Send the email
        with smtplib.SMTP('smtp.gmail.com', 587) as server:
            server.starttls()
            server.login(sender_email, password)
            text = message.as_string()
            server.sendmail(sender_email, receiver_emails, text)
            print('Email sent successfully!')
    
urls = ['https://production.techsavanna.technology/fgshr', 
        'http://ippf.s3-website-eu-west-1.amazonaws.com',
        'https://halfpricedbooks.co.ke/pos',
        'http://34.201.242.31/tims/admin/login.php',
        'http://techsavanna.technology/tifms',
        'https://techsavanna.technology/technoserve',
        'https://techsavanna.technology/flash-gym',
        'https://techsavanna.technology/flash-spa',
        'https://powergas.techsavanna.technology',
        "https://techsavanna.technology/river-court-palla/",
        'https://production.techsavanna.technology/merudiocesehr',
        'https://production.techsavanna.technology/kidogoadmin',
        'https://techsavanna.technology/zurisana',
        'https://techsavanna.technology/zurisana-cafe',
        'https://techsavanna.technology/justdrinks'
        ]


for url in urls:
    # Execute wget command and capture the output
    command = ['wget', url]
    # command = ['wget', 'http://3.83.128.211/proximity_sb2/public/api/distance']
    output_file = 'MyOutputFile'

    try:
        output = subprocess.check_output(command, stderr=subprocess.STDOUT, universal_newlines=True)

        status_code_match = re.findall(r"HTTP request sent, awaiting response\.\.\. (\d+)", output)
        response_type_match = re.search(r"Length: unspecified \[(.+)\]", output)

        last_status_code = status_code_match[-1]   
        status_code = int(last_status_code)

        length_match = re.search(r"Length: (\d+) \[(\w+/\w+)\]", output)
    
        if response_type_match:
            response_type_match = response_type_match.group(1)

        else:
            
            response_type_pattern =  re.findall(r"\[([^]]+)\]", output)

            response_type_match = response_type_pattern[-2]
            # None

        if length_match:
            length_match = float(length_match.group(1))
        
        else:
            
            file_size_match = re.search( r"\[\s*(\d+(\.\d+)?)\s*", output)
            if file_size_match:
                length_match = float(file_size_match.group(1))

    
        if status_code_match:
            
            if 200 <= status_code <= 300 and response_type_match == "text/html" and length_match:
                
                if length_match > 0:
                    print("Status code:",status_code ,"Response type:",response_type_match,"length:",length_match)
            else:
                subject="Application Warning"
                body= "Application: '{}'  Returning a blank page".format(command[1])
                sendEmail(subject,body)
                myfile= re.search(r"/([^/]+)$", command[1]).group(1)
        
                if os.path.exists(myfile):
                    os.remove(myfile)
                    print("File deleted:", myfile)
        # Delete the downloaded file
                else:
                    os.remove("index.html")
    
        # myfile= re.search(r"/([^/]+)$", command[1]).group(1)
        
        parsed_url = urlparse(url)
        myfile = parsed_url.path.strip("/").split("/")[-1]
        if os.path.exists(myfile):
            os.remove(myfile)
            print("File deleted:", myfile)
        else:
            os.remove("index.html")
            print("File deleted: index.html")



    except subprocess.CalledProcessError as e:
        # print("hi")
        subject= 'Application Critical Error report'
        body="Aplication tested by Command '{}'  Error: {}".format(e.cmd, e.output.strip().split('\n')[-1])
        sendEmail(subject,body)
