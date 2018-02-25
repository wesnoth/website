DOMAIN = wesnoth-start-1.12

PODIR = po
STAMPDIR = .stamp

LANGUAGES = $(shell cd $(PODIR) && ls *.po | sed 's/\.po//')

POT = $(PODIR)/$(DOMAIN).pot
PO = $(addprefix $(PODIR)/, $(LANGUAGES:=.po))

TEMPLATE = template.html
PAGE = index
TMAP = $(PAGE).var
PAGE_STAMP = $(STAMPDIR)/$(PAGE)
HTML_PAGES = $(addprefix $(PAGE_STAMP)., $(LANGUAGES))
HTML_STAMP = $(HTML_PAGES:=.html)

LIST = languages.conf
LIST_STAMP = $(STAMPDIR)/$(LIST)

PO4A_OPTIONS = -f xhtml -M utf-8 -o untranslated='<script>'
PO4A_THRESHOLD = 85

# ----------------------------------------------------------------------

all: update-pot update-po update-html update-list

update-pot: $(POT)

update-po: $(PO)

update-html: $(HTML_STAMP) $(PAGE).en.html

update-list: $(LIST_STAMP)

clean:
	rm -rf $(PODIR)/*~ $(PAGE).* $(TMAP) $(STAMPDIR)

# ----------------------------------------------------------------------

$(POT): $(TEMPLATE)
	po4a-gettextize $(PO4A_OPTIONS) -L utf-8 -m $< -p $@

$(PO): $(TEMPLATE)
	dos2unix $@
	po4a-updatepo $(PO4A_OPTIONS) -m $< -p $@
	@touch $@

$(PAGE).en.html:
	cp $(TEMPLATE) $@

$(PAGE_STAMP).%.html: $(TEMPLATE) $(PODIR)/%.po
	@html=`basename $@` && \
	lang=`echo $$html | sed 's/$(PAGE).//;s/.html//'` && \
	echo $$html && \
	po4a-translate $(PO4A_OPTIONS) -L utf-8 -m $(TEMPLATE) \
	               -k $(PO4A_THRESHOLD) -p $(PODIR)/$$lang.po -l $$html
	@mkdir -p $(STAMPDIR) && touch $@

$(LIST_STAMP): $(LIST) $(HTML_STAMP)
	@list=$$(for i in $(PAGE).*.html; do \
		code=`echo $$i | cut -d. -f 2`; \
		lang=`echo $$code | tr "A-Z_" "a-z-"`; \
		name=`grep "^$$code:" $(LIST) | cut -d: -f 2`; \
		echo -n "<li lang='$$lang'><a href='$$i'>$$name</a></li>"; \
	done) && \
	for i in $(PAGE).*.html; do \
		sed -i "/<!--LANGS-->./d; \
		        s,<!--LANGS-->,<!--LANGS-->$$list\n<!--LANGS-->," $$i; \
	done
	@for i in $(PAGE).*.html; do \
		lang=`echo $$i | cut -d. -f 2 | tr "A-Z_" "a-z-"`; \
		sed -i "s,<html lang='.[^']*'>,<html lang='$$lang'>," $$i ; \
		echo "URI: $$i"; \
		echo "Content-type: text/xhtml"; \
		echo "Content-language: $$lang"; \
		echo ""; \
	done > $(TMAP)
	@mkdir -p $(STAMPDIR) && touch $@

# ----------------------------------------------------------------------

stats:
	@cd $(PODIR) && \
	for i in *.po; do \
		echo -n "$$i: "; \
		msgfmt --statistics -o /dev/null $$i; \
	done

.PHONY: all update-pot update-po update-html update-list clean stats
