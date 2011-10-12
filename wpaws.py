import sys
import _mysql
from mako.template import Template
import subprocess as sub
from subprocess import Popen
from datetime import datetime
import MySQLdb as dbm

def render2file(domain,template):
    f = file("awstats."+domain+".conf", "w")
    f.write(template.render(domain=domain))
    f.close()


def update_templates():
    db=dbm.connect(user="blogs",passwd="JAC92@lib",db="blogs")
    store=db.cursor()
    store.execute("SELECT w.domain FROM wp_blogs w")
    data=store.fetchall()
    mytemplate = Template(filename='./awstats.conf.tmpl')
    db.close()
    for row in data:
	print row[0]
        render2file(row[0],mytemplate)
    print "done"
    
def update_reports():
    db=dbm.connect(user="blogs",passwd="JAC92@lib",db="blogs")
    store=db.cursor()
    store.execute("SELECT w.domain, w.blog_id FROM wp_blogs w")
    data=store.fetchall() 
    query=''
    db.close();
    for row in data:
	domain, id = row
        query += "select "+str(id)+",'"+str(domain)+"' from wp_"+str(id)+"_options as opt1 where opt1.option_name = 'active_plugins' and opt1.option_value like '%wpaws.php%' union "

    db=dbm.connect(user="blogs",passwd="JAC92@lib",db="blogs")
    store2 = db.cursor()
    store2.execute(query[:-6])
    data2 = store2.fetchall()
    store3 = db.cursor()
    for row in data2:
	id, domain = row
	print domain, id
        reportcmd = ['perl', '/usr/lib/cgi-bin/awstats.pl', '-config=%s'%domain, '-output', '-staticlinks']
        p=Popen(reportcmd,stdout=sub.PIPE)
	output, error=p.communicate()
	#data = (id,datetime.now(),output)
        store3.execute("INSERT INTO wp_wpaws (rblogid,time,report) VALUES (%s,date(%s),%s) ON DUPLICATE KEY UPDATE time=VALUES(time), report=VALUES(report)",(id,str(datetime.now())[:-7],output.replace("""<td class="aws_blank">&nbsp;</td>""", '', 18)))

    db.close()

    print "done"


if (len(sys.argv) > 1):
    if( sys.argv[1] == '--update-templates' ):
        update_templates()
    elif( sys.argv[1] == '--update-staticpages' ):
        print "Guten Tag Welt!"
    elif( sys.argv[1] == '--update-all-staticpages' ):
        print "Guten Tag Welt!"
    elif( sys.argv[1] == '--update-reports' ):
        update_reports()
    elif( sys.argv[1] == '--update-all-stats' ):
        print "Guten Tag Welt!"

    else:
        print "Which language?"
        print "Welche sprache?"
else:
    print "Wordpress-Awstats integration script"
    print "Usage: " +sys.argv[0]+ " [options] "

