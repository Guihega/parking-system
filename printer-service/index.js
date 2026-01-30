const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const escpos = require('escpos');

escpos.USB = require('escpos-usb');

const app = express();

app.use(cors());
app.use(bodyParser.json());

app.post('/print', (req, res) => {
    try {

        const device = new escpos.USB();   // detecta impresora USB por default
        const printer = new escpos.Printer(device);

        const t = req.body;

        device.open(() => {

            printer
                .align('CT')
                .style('B')
                .text('PARKING SYSTEM')
                .style('NORMAL')
                .text(t.branch || '')
                .text(t.datetime || '')
                .drawLine()

                .align('LT')
                .text(`Folio: ${t.folio}`)
                .text(`Placa: ${t.plate}`)
                .text(`Cajón: ${t.space}`)
                .text(`Entrada: ${t.entry}`)
                .text(`Salida: ${t.exit}`)
                .text(`Tiempo: ${t.minutes} min`)

                .drawLine()
                .align('CT')
                .style('B')
                .text(`TOTAL $${t.total}`)
                .style('NORMAL')
                .text(`Pago: ${t.payment}`)

                .drawLine()
                .qrimage(t.folio, { size: 6 })

                .text('\nGracias por su visita\n')
                .cut()
                .close();
        });

        res.json({ ok: true });

    } catch (err) {
        console.error('PRINT ERROR:', err);
        res.status(500).json({ error: err.message });
    }
});

app.listen(3000, () => {
    console.log('🖨 Printer service running on http://localhost:3000');
});
