<table width="99%" cellpadding="2" cellspacing="2" align="center" style="font-size:10pt;background-color:white;">
<tr><td>
<h2>
Suchbegriffe
</h2>
Sie können einen oder mehrere Begriffe für eine Volltextsuche verwenden.
<br>
Mit Hilfe verschiedener Operatoren können Sie die Suchmöglichkeiten erweitern. Es werden die folgenden Operatoren unterstützt:
</td>
</tr>
</table>
<br>
<table width="99%" cellpadding="2" cellspacing="2" align="center" style="font-size:10pt;background-color:lightgrey;">
<tr>
<td align="center">+</td><td>Ein vorangestelltes Pluszeichen zeigt an, dass ein Wort vorhanden sein <b>muss</b>.</td>
</tr>
<tr>
<td align="center">-</td><td>Ein vorangestelltes Minuszeichen zeigt an, dass ein Wort <b>nicht vorkommen darf</b>.</td>
</tr>
<tr>
<td align="center">&lt;&nbsp;&gt;</td><td>Mit diesen beiden Operatoren beeinflussen Sie die <b>Relevanz</b>

eines Wortes für die Bewertung des Suchergebnisses. Die Suchergebnisse
werden nach Relevanz sortiert. Ein vorangestelltes "&lt;" mindert die
Relevanz, ein vorangestelltes "&gt;" erhöht sie.</td>
</tr>
<tr>
<td align="center">(&nbsp;)</td><td>Mit Klammern können Sie Teilausdrücke gruppieren.</td>
</tr>
<tr>
<td align="center">~</td><td>Eine vorangestellte Tilde fungiert als
Verneinungs-Operator, der den Relevanzbeitrag eines Wortes umkehrt.
Damit wird ein Wort niedriger bewertet als andere, aber nicht
ausgeschlossen.</td>
</tr>
<tr>
<td align="center">*</td><td>Ein <b>nachgestellter</b> Stern bewirkt eine Trunkierung.</td>
</tr>
<tr>
<td align="center">"</td><td>Mit Anführungszeichen können Sie zusammenhängende Ausdrücke suchen, es wird exakt nach dem eingebenen Ausdruck gesucht.</td>
</tr>
</table>
<br>
<table width="99%" cellpadding="2" cellspacing="2" align="center" style="font-size:10pt;background-color:white;">
<tr>
<td colspan="2"><b>Suchbeispiele:</b></td>
</tr>
<tr bgcolor="#ffcc99">
<td nowrap="nowrap">Arbeitsmarkt Deutschland</td><td>findet Datensätze, in denen "Arbeitsmarkt" oder "Deutschland" oder beide Begriffe vorkommen.</td>
</tr>
<tr bgcolor="#ffcc99">
<td nowrap="nowrap">+Arbeitsmarkt +Deutschland</td><td>findet Datensätze, in denen sowohl "Arbeitsmarkt" als auch "Deutschland" vorkommen.</td>
</tr>
<tr bgcolor="#ffcc99">
<td nowrap="nowrap">+Arbeitsmarkt Deutschland</td><td>findet Datensätze, in denen "Arbeitsmarkt" vorkommt. Kommt zusätzlich "Deutschland" vor, wird die Relevanz erhöht.</td>

</tr>
<tr bgcolor="#ffcc99">
<td nowrap="nowrap">+Arbeitsmarkt -Deutschland</td><td>findet Datensätze, in denen "Arbeitsmarkt", aber nicht "Deutschland" vorkommt.</td>
</tr>
<tr bgcolor="#ffcc99">
<td nowrap="nowrap">+Arbeitsmarkt +(&gt;Deutschland &lt;Europa)</td><td>findet
Datensätze, in denen sowohl "Arbeitsmarkt" als auch "Deutschland" oder sowohl "Arbeitsmarkt"
als auch "Europa" vorkommt; die erste Kombination erhält aber eine
höhere Relevanz.</td>
</tr>
<tr bgcolor="#ffcc99">
<td nowrap="nowrap">Arbeitsmarkt*</td><td>findet auch "Arbeitsmarktsituation"</td>

</tr>

<tr bgcolor="#ffcc99">
<td nowrap="nowrap">"Arbeitsmarkt- und Berufsforschung"</td><td>findet nur "Arbeitsmarkt- und Berufsforschung", nicht "Arbeitsmarkt, Berufsforschung"</td>
</tr>
</table>
<br>