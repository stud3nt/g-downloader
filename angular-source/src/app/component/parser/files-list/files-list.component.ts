import { Component, Input, OnInit } from '@angular/core';
import { ParserRequest } from "../../../model/parser-request";
import { ParsedFile } from "../../../model/parsed-file";
import { NodeFileService } from "../../../service/node-file.service";
import { FileStatus } from "../../../enum/file-status";
import { FileType } from "../../../enum/file-type";
import { ModalDataService } from "../../../service/data/modal-data.service";
import { ModalSize } from "../../../enum/modal-size";
import { ModalType } from "../../../enum/modal-type";
import { DownloaderDataService } from "../../../service/data/downloader-data.service";
import { ToastrDataService } from "../../../service/data/toastr-data.service";
import { WebSocketService } from "../../../service/web-socket.service";
import { JsonResponse } from "../../../model/json-response";
import { Status } from "../../../model/status";
import { WebsocketOperation } from "../../../enum/websocket-operation";
import { AuthService } from "../../../service/auth.service";

@Component({
  selector: 'app-files-list',
  templateUrl: './files-list.component.html'
})
export class FilesListComponent implements OnInit {

	@Input() parserRequest: ParserRequest;

	public FileStatus = FileStatus;
	public FileType = FileType;

	public lockTiles = false;

	private _webSocketName = 'file_loader_websocket';

	constructor(
		protected nodeFileService: NodeFileService,
		protected downloaderDataService: DownloaderDataService,
		protected toastrService: ToastrDataService,
		protected auth: AuthService,
		protected webSocketService: WebSocketService,
		protected modal: ModalDataService
	) {}

	ngOnInit() {}

	public determineFileClass(file: ParsedFile) : string {
		let nodeClass = 'tile tile-250';

		if (file.hasStatus(FileStatus.Queued))
			nodeClass += ' queued';

		if (file.hasStatus(FileStatus.Downloaded))
			nodeClass += ' downloaded';

		return nodeClass;
	}

	/**
	 * Toggle file in queue (removes or adds);
	 *
	 * @param file
	 */
	public toggleFileQueue(file: ParsedFile) : void {
		if (this.lockTiles || file.hasStatus(FileStatus.Waiting))
			return;

		file.parentNode = this.parserRequest.currentNode;
		file.addStatus(FileStatus.Waiting);

		this.nodeFileService.toggleFileQueue(file).subscribe((result: ParsedFile) => {
			file.removeStatus(FileStatus.Waiting);

			if (result.hasStatus(FileStatus.Queued))
				file.addStatus(FileStatus.Queued);
			else
				file.removeStatus(FileStatus.Queued);

			this.downloaderDataService.touch();
		}, (error) => {
			file.removeStatus(FileStatus.Waiting);
			this.toastrService.addError('An error occured.');
		});
	}

	public openFilePreview(file: ParsedFile) : void {
		let modalTitle = file.title ? file.title : (file.name+'.'+file.extension);

		this.modal.open(ModalType.Preview, modalTitle)
			.showLoader()
			.setLoaderText('Loading...');

		this.nodeFileService.toggleFilePreview(file).subscribe((result: ParsedFile) => {
			if (result.width < 600)
				this.modal.setSize(ModalSize.Small);
			else if (result.width > 600 && result.width < 1000)
				this.modal.setSize(ModalSize.Medium);
			else
				this.modal.setSize(ModalSize.Large);

			this.modal.setBody(result.htmlPreview).hideLoader();
		});

		if (this.webSocketService.isConnected(this._webSocketName))
			this.webSocketService.disconnect(this._webSocketName);

		this.webSocketService.createListener(this._webSocketName,
			(response) => {
				if (typeof response === 'object') {
					let jsonResponse = new JsonResponse(response);

					if (jsonResponse.success()) {
						let status = new Status(jsonResponse.data);

						if (status.progress < 100) {
							this.modal.setLoaderText(status.progress+'%');

							setTimeout(() => {
								this.sendPreviewStatusRequest(file);
							}, 250);
						} else  {
							this.modal.setLoaderText('DONE.');
						}
					}
				}
			}, (error) => {
				this.toastrService.addError('PARSER ERROR', error.message);
				console.log(error);
			}, () => {

			}
		);

		this.sendPreviewStatusRequest(file);
	}

	public sendPreviewStatusRequest(file: ParsedFile): void {
		this.webSocketService.sendRequest(
			this._webSocketName,
			WebsocketOperation.DownloadFileStatus,
			this.auth.user.apiToken,
			file
		);
	};

}
