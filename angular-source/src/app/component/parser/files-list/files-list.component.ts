import {Component, Input, OnDestroy, OnInit} from '@angular/core';
import { ParserRequest } from "../../../model/parser-request";
import { ParsedFile } from "../../../model/parsed-file";
import { NodeFileService } from "../../../service/node-file.service";
import { FileStatus } from "../../../enum/file-status";
import { FileType } from "../../../enum/file-type";
import { ModalType } from "../../../enum/modal-type";
import { DownloaderDataService } from "../../../service/data/downloader-data.service";
import { ToastrDataService } from "../../../service/data/toastr-data.service";
import { WebSocketService } from "../../../service/web-socket.service";
import { JsonResponse } from "../../../model/json-response";
import { Status } from "../../../model/status";
import { WebsocketOperation } from "../../../enum/websocket-operation";
import { AuthService } from "../../../service/auth.service";
import { ModalService } from "../../../service/modal.service";

@Component({
  selector: 'app-files-list',
  templateUrl: './files-list.component.html'
})
export class FilesListComponent implements OnInit, OnDestroy {

	@Input() parserRequest: ParserRequest;

    @Input() set tileHeight(tileHeight: number) {
        if (tileHeight > 0)
            this._tileHeight = tileHeight;
    }

	public FileStatus = FileStatus;
	public FileType = FileType;

	public lockTiles = false;

	public _previewModalId: string = 'file-preview-modal';
	public _previewModalTitle: string = '';
	public _previewModalContent: string = '';

	public _tileHeight: number = 0;

	public _previewFile: ParsedFile = null;
	public _previewClass: string = 'files-preview';

	public ModalType = ModalType;

	private _webSocketName = 'file_loader_websocket';

	constructor(
		protected nodeFileService: NodeFileService,
		protected downloaderDataService: DownloaderDataService,
		protected toastrService: ToastrDataService,
		protected auth: AuthService,
		protected webSocketService: WebSocketService,
		protected modalService: ModalService
	) {}

	ngOnInit() {}

	ngOnDestroy(): void {
	    this.modalService.remove(this._previewModalId);
    }

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
     * @param forced: boolean
	 */
	public toggleFileQueue(file: ParsedFile, forced: boolean = false) : void {
		if ((this.lockTiles || file.hasStatus(FileStatus.Waiting) || file.hasStatus(FileStatus.Downloaded)
            || file.hasStatus(FileStatus.Queued) && !forced))
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
		let modalTitle = ((file.title && file.title !== 'null') ? file.title : (file.name+'.'+file.extension));
		let recursive = true;

		this._previewFile = file;
		this.modalService.selectModal(this._previewModalId)
            .open()
			.setTitle(modalTitle)
			.showLoader(true);

		if (this.webSocketService.isConnected(this._webSocketName))
			this.webSocketService.disconnect(this._webSocketName);

		this.webSocketService.createListener(this._webSocketName,
			(response) => {
				if (typeof response === 'object') {
					let jsonResponse = new JsonResponse(response);

					if (jsonResponse.success()) {
						let status = new Status(jsonResponse.data);

						if (status.progress < 100) {
							this.modalService.setLoaderText(status.progress+'%');

							setTimeout(() => {
								this.sendPreviewStatusRequest(file);
							}, 250);
						} else  {
							this.modalService.setLoaderText('DONE.');
							recursive = false;
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

        this.nodeFileService.toggleFilePreview(file).subscribe((result: ParsedFile) => {
            this._previewModalContent = result.htmlPreview;
            this.modalService.hideLoader();
        });
	}

	public toggleFilePreviewMode(): void {
		if (this._previewClass === 'files-preview')
			this._previewClass = 'files-preview enlargement-content';
		else
			this._previewClass = 'files-preview';
	}

	public closeFilePreview(): void {
		this._previewFile = null;
		this._previewClass = 'files-preview';
	}

	public savePreviewedFile(): void {
		if (this._previewFile.parentNode === null)
			this._previewFile.parentNode = this.parserRequest.currentNode;

		this.nodeFileService.downloadFilePreview(this._previewFile).subscribe((parsedFile: ParsedFile) => {
			this.parserRequest.updateFile(parsedFile);
		}, (error) => {
			this.toastrService.addError('An error occured.');
		});
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
