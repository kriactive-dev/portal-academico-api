import { Card, CardContent } from "@workspace/ui/components/card"

import { TaxaForm } from "../taxa-form"

export default function NovaTaxaPage() {
  return (
    <div className="flex flex-col gap-4">
      <div>
        <h1 className="text-2xl font-semibold">Nova Taxa</h1>
        <p className="text-sm text-muted-foreground">
          Criar uma nova taxa ou emolumento.
        </p>
      </div>
      <Card>
        <CardContent>
          <TaxaForm />
        </CardContent>
      </Card>
    </div>
  )
}
