import {
  Card,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@workspace/ui/components/card"

export default function InicioPage() {
  return (
    <div className="flex flex-1 flex-col gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Bem-vindo</CardTitle>
          <CardDescription>
            Portal do estudante — em construção.
          </CardDescription>
        </CardHeader>
      </Card>
    </div>
  )
}
